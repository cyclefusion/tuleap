<?php
/**
 * Copyright (c) Enalean, 2014. All Rights Reserved.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

namespace Tuleap\User\REST\v1;

use \UserManager;
use \Tuleap\User\REST\UserRepresentation;
use \Tuleap\REST\Header;
use \Luracast\Restler\RestException;

/**
 * Wrapper for users related REST methods
 */
class UserResource {

    /** @var UserManager */
    private $user_manager;

    public function __construct() {
        $this->user_manager = UserManager::instance();
    }

    /**
     * Get a user
     *
     * Get the definition of a given user
     *
     * @url GET {id}
     *
     * @param int $id Id of the desired user
     *
     * @access public
     *
     * @throws 400
     * @throws 403
     * @throws 404
     *
     * @return \Tuleap\User\REST\UserRepresentation
     */
    public function getId($id) {

        $this->checkUserExists($id);
        $user_representation = new UserRepresentation();
        $user_representation->build($this->user_manager->getUserById($id));

        return $user_representation;
    }

    /**
     * @url OPTIONS {id}
     *
     * @param int $id Id of the user
     *
     * @access public
     *
     * @throws 400
     * @throws 404
     */
    public function optionsId($id) {
        $this->checkUserExists($id);
        $this->sendAllowHeadersForUserGroup();
    }

    private function checkUserExists($id) {
        $user = $this->user_manager->getUserById($id);

        if (! $user) {
            throw new RestException(404, 'User Id not found');
        }

        return true;
    }

    private function sendAllowHeadersForUser() {
        Header::allowOptionsGet();
    }
}