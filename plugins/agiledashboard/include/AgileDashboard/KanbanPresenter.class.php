<?php
/**
 * Copyright (c) Enalean, 2014. All Rights Reserved.
 *
 * This file is a part of Tuleap.
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
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

class KanbanPresenter {

    /** @var string */
    public $language;

    /** @var string */
    public $kanban_name;

    /** @var int */
    public $kanban_open;

    /** @var int */
    public $kanban_closed;

    /** @var int */
    public $tracker_id;

    /** @var boolean */
    public $user_is_kanban_admin;

    public function __construct(AgileDashboard_Kanban $kanban, $user_is_kanban_admin, $language) {
        $this->language             = $language;
        $this->kanban_name          = $kanban->getName();
        $this->kanban_open          = 0;
        $this->kanban_closed        = 0;
        $this->kanban_id            = $kanban->getId();
        $this->user_is_kanban_admin = (int) $user_is_kanban_admin;
    }
}
