<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 *
 * This file is a part of Codendi.
 *
 * Codendi is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Codendi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Codendi. If not, see <http://www.gnu.org/licenses/>.
 */

require_once('include/DataAccessObject.class.php');

class ProjectDao extends DataAccessObject {

    const TABLE_NAME       = 'groups';
    const GROUP_ID         = 'group_id';
    const STATUS           = 'status';
    const UNIX_GROUP_NAME  = 'unix_group_name';
    const IS_PUBLIC        = 'is_public';
   

    public function __construct($da) {
        parent::__construct($da);
        $this->table_name = 'groups';
    }

    public function searchById($id) {
        $sql = "SELECT *".
               " FROM ".$this->table_name.
               " WHERE group_id = ".$this->da->quoteSmart($id);
        return $this->retrieve($sql);
    }

    public function searchByStatus($status) {
        $status = $this->da->quoteSmart($status);
        $sql = "SELECT *
                FROM $this->table_name
                WHERE status = $status";
        return $this->retrieve($sql);
    }
    
    public function searchByUnixGroupName($unixGroupName){
        $unixGroupName= $this->da->quoteSmart($unixGroupName);
        $sql = "SELECT * 
                FROM $this->table_name
                WHERE unix_group_name=$unixGroupName";
        return $this->retrieve($sql);
    }

    /**
     * Look for active projects, based on their name (unix/public)
     * 
     * This method returns only active projects. If no $userId provided, only
     * public project are returned.
     * If $userId is provided, both public and private projects the user is member
     * of are returned
     * If $userId is provided, you can also choose to restrict the result set to
     * the projects the user is member of or is admin of.
     * 
     * @param String  $name
     * @param Integer $limit
     * @param Integer $userId
     * @param Boolean $isMember
     * @param Boolean $isAdmin
     * 
     * @return DataAccessResult
     */
    public function searchProjectsNameLike($name, $limit, $userId=null, $isMember=false, $isAdmin=false) {
        $join    = '';
        $where   = '';
        $groupby = '';
        if ($userId != null) {
            if ($isMember || $isAdmin) {
                // Manage if we search project the user is member or admin of
                $join  .= ' JOIN user_group ug ON (ug.group_id = g.group_id)';
                $where .= ' AND ug.user_id = '.$this->da->escapeInt($userId);
                if ($isAdmin) {
                    $where .= ' AND ug.admin_flags = "A"';
                }
            } else {
                // Either public projects or private projects the user is member of
                $join  .= ' LEFT JOIN user_group ug ON (ug.group_id = g.group_id)';
                $where .= ' AND (g.is_public = 1'.
                          '      OR (g.is_public = 0 and ug.user_id = '.$this->da->escapeInt($userId).'))';
            }
            $groupby .= ' GROUP BY g.group_id';
        } else {
            // If no user_id provided, only return public projects
            $where .= ' AND g.is_public = 1';
        }

        $sql = "SELECT SQL_CALC_FOUND_ROWS g.*".
               " FROM ".$this->table_name." g".
               $join.
               " WHERE (g.group_name like ".$this->da->quoteSmart($name.'%').
               " OR g.unix_group_name like ".$this->da->quoteSmart($name.'%').")".
               " AND g.status='A'".
               $where.
               $groupby.
               " ORDER BY group_name".
               " LIMIT ".$this->da->escapeInt($limit);
        return $this->retrieve($sql);
    }

    /**
     * Update the http_domain and service when renaming the group
     * @param Project $project
     * @param String  $new_name
     * @return Boolean
     */
    public function renameProject($project,$new_name){
        //Update 'groups' table
        $sql = ' UPDATE groups SET unix_group_name= '.$this->da->quoteSmart($new_name).' , 
                 http_domain=REPLACE (http_domain,'.$this->da->quoteSmart($project->getUnixName(false)).','.$this->da->quoteSmart($new_name).')
                 WHERE group_id= '.$this->da->quoteSmart($project->getID());
        $res_groups = $this->update($sql);

        //Update 'service' table
        if ($res_groups){
            $sql_summary  = ' UPDATE service SET link= REPLACE (link,'.$this->da->quoteSmart($project->getUnixName()).','.$this->da->quoteSmart(strtolower($new_name)).')
                              WHERE short_name="summary"
                              AND group_id= '.$this->da->quoteSmart($project->getID());
            $res_summary = $this->update($sql_summary);
            if ($res_summary){
                $sql_homePage = ' UPDATE service SET link= REPLACE (link,'.$this->da->quoteSmart($project->getUnixName()).','.$this->da->quoteSmart(strtolower($new_name)).')
                                  WHERE short_name="homepage"
                                  AND group_id= '.$this->da->quoteSmart($project->getID());
                return $this->update($sql_homePage);
            }
        }
        return false;
    }
    
    /**
     * Return all projects matching given parameters
     * 
     * @param Integer $offset
     * @param Integer $limit
     * @param String  $status
     * @param String  $groupName
     * @return Array
     */
    public function returnAllProjects($offset, $limit, $status=false, $groupName=false) {
        $cond = array();
        if ($status != false) {
            $cond[] = 'status='.$this->da->quoteSmart($status);
        }
        
        if ($groupName != false) {
            $cond[] = 'group_name LIKE '.$this->da->quoteSmart($groupName.'%');
        }
        
        if (count($cond) > 0) {
            $stm = ' WHERE '.implode(' AND ', $cond);
        } else {
            $stm = '';
        }
        
        $sql = 'SELECT SQL_CALC_FOUND_ROWS *
                FROM groups '.$stm.'
                ORDER BY group_name 
                ASC LIMIT '.$this->da->escapeInt($offset).', '.$this->da->escapeInt($limit);

        $res = db_query($sql);
        $sql = 'SELECT FOUND_ROWS() as nb';
        $res_numrows = db_query($sql);
        $row = db_fetch_array($res_numrows);
        return array('projects' => $res, 'numrows' => $row['nb']);
    }


    public function searchByPublicStatus($IsPublic){
        $IsPublic= $this->da->quoteSmart($IsPublic);
        $sql = "SELECT group_id
                FROM $this->table_name
                WHERE is_public=$IsPublic
                AND status = 'A'";
        return $this->retrieve($sql);
    }
    
    /**
     * Filled the ugroups to be notified when admin action is needed
     *
     * @param Integer $groupId
     * @param Array   $ugroups
     *
     * @return Boolean
     */
    public function setMembershipRequestNotificationUGroup($groupId, $ugroups){
        $sql = ' DELETE FROM groups_notif_delegation WHERE group_id ='.$this->da->quoteSmart($groupId);
        if (!$this->update($sql)) {
            return false;
        }
        foreach ($ugroups as $ugroupId) {
            $sql = ' INSERT INTO groups_notif_delegation (group_id, ugroup_id)
                 VALUE ('.$this->da->quoteSmart($groupId).', '.$this->da->quoteSmart($ugroupId).') 
                 ON DUPLICATE KEY UPDATE ugroup_id = '.$this->da->quoteSmart($ugroupId);
            if (!$this->update($sql)) {
                return false;
            }
        }
        return true;
    }

     /**
     * Returns the ugroup to be notified when admin action is needed for given project
     * 
     * @param Integer $groupId
     * @param Integer $ugroups
     * 
     * @return DataAccessResult
     */
    public function getMembershipRequestNotificationUGroup($groupId){
        $sql = ' SELECT ugroup_id FROM groups_notif_delegation WHERE group_id = '.$this->da->quoteSmart($groupId);
        return $this->retrieve($sql);
    }
    
   
}

?>
