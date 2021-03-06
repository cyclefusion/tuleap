<?php
/**
 * Copyright (c) Enalean, 2013. All Rights Reserved.
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

class OpenId_OpenIdRouter {
    /** @var Logger */
    private $logger;

    const LOGIN                = 'login';
    const FINISH_LOGIN         = 'finish_login';
    const PAIR_ACCOUNTS        = 'pair_accounts';
    const FINISH_PAIR_ACCOUNTS = 'finish_pair_accounts';
    const REMOVE_PAIR          = 'remove_pair';
    const SHOW_PAIR_ACCOUNTS   = 'show_pair_accounts';

    private $routes = array(
        self::LOGIN,
        self::FINISH_LOGIN,
        self::PAIR_ACCOUNTS,
        self::FINISH_PAIR_ACCOUNTS,
        self::REMOVE_PAIR,
        self::SHOW_PAIR_ACCOUNTS,
    );

    public function __construct(Logger $logger) {
        $this->logger = $logger;
    }

    public function route(HTTPRequest $request, Layout $response) {
        $valid_route  = new Valid_WhiteList('func', $this->routes);
        $valid_route->required();
        if ($request->valid($valid_route)) {
            $route = $request->get('func');
            $controller = new OpenId_LoginController(
                $this->logger,
                new OpenId_AccountManager(
                    new Openid_Dao(),
                    UserManager::instance()
                ),
                $request,
                $response
            );
            $controller->$route();
        } else {
            $response->addFeedback(Feedback::ERROR, 'Invalid request for '.__CLASS__);
            $response->redirect('/');
        }
    }
}

?>
