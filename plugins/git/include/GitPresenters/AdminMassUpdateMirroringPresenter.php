<?php
/**
 * Copyright (c) Enalean, 2014. All rights reserved
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/
 */

class GitPresenters_AdminMassUdpdateMirroringPresenter {

    /**
     * @var GitPresenters_MirrorPresenter[]
     */
    public $mirror_presenters;

    public function __construct(array $mirror_presenters) {
        $this->mirror_presenters = $mirror_presenters;
    }

    public function mirroring_title() {
        return $GLOBALS['Language']->getText('plugin_git', 'mirroring_title');
    }

    public function mirroring_info() {
        return $GLOBALS['Language']->getText('plugin_git', 'view_admin_mass_update_mirroring');
    }

    public function mirroring_mirror_name() {
        return $GLOBALS['Language']->getText('plugin_git', 'mirroring_mirror_name');
    }

    public function mirroring_mirror_url() {
        return $GLOBALS['Language']->getText('plugin_git', 'mirroring_mirror_url');
    }
}
