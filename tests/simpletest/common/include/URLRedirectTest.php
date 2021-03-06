<?php

/**
 * Copyright (c) Enalean, 2014-2015. All Rights Reserved.
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
class URLRedirect_MakeUrlTest extends TuleapTestCase {

    private $request;
    private $url_redirect;

    public function setUp() {
        $this->url_redirect = new URLRedirect();
        $this->request = mock('HTTPRequest');
        $GLOBALS['sys_force_ssl'] = 1;
        $GLOBALS['sys_https_host'] = 'example.com';
        $GLOBALS['sys_default_domain'] = 'example.com';
        parent::setUp();
    }

    public function tearDown() {
        unset($GLOBALS['sys_force_ssl']);
        unset($GLOBALS['sys_https_host']);
        unset($GLOBALS['sys_default_domain']);
        parent::tearDown();
    }

    public function itStayInSSLWhenForceSSLIsOn() {
        stub($this->request)->isSSL()->returns(true);
        $GLOBALS['sys_force_ssl'] = 1;

        $this->assertEqual('/my/index.php', $this->url_redirect->makeReturnToUrl($this->request, '/my/index.php'));
    }

    public function itRedirectToHttpWhenForceSSLIsOffAndNoStayInSSL() {
        stub($this->request)->isSSL()->returns(true);
        $GLOBALS['sys_force_ssl'] = 0;
        stub($this->request)->existAndNonEmpty('stay_in_ssl')->returns(true);
        stub($this->request)->get('stay_in_ssl')->returns(0);

        $this->assertEqual('http://example.com/my/index.php', $this->url_redirect->makeReturnToUrl($this->request, '/my/index.php'));
    }

    public function itRedirectToHttpWhenForceSSLIsOffAndNoStayInSSL2() {
        stub($this->request)->isSSL()->returns(true);
        $GLOBALS['sys_force_ssl'] = 0;
        stub($this->request)->existAndNonEmpty('stay_in_ssl')->returns(false);
        stub($this->request)->get('stay_in_ssl')->returns(false);

        $this->assertEqual('http://example.com/my/index.php', $this->url_redirect->makeReturnToUrl($this->request, '/my/index.php'));
    }

    public function itStayInSSLWhenForceSSLIsOffAndNoStayInSSL() {
        stub($this->request)->isSSL()->returns(true);
        $GLOBALS['sys_force_ssl'] = 0;
        stub($this->request)->existAndNonEmpty('stay_in_ssl')->returns(true);
        stub($this->request)->get('stay_in_ssl')->returns(1);

        $this->assertEqual('/my/index.php', $this->url_redirect->makeReturnToUrl($this->request, '/my/index.php'));
    }

    public function itStayUnencryptedWhenForceSSLIsOffAndNoStayInSSL() {
        stub($this->request)->isSSL()->returns(false);
        $GLOBALS['sys_force_ssl'] = 0;
        stub($this->request)->existAndNonEmpty('stay_in_ssl')->returns(true);
        stub($this->request)->get('stay_in_ssl')->returns(0);

        $this->assertEqual('/my/index.php', $this->url_redirect->makeReturnToUrl($this->request, '/my/index.php'));
    }

    public function itNotRedirectToUntrustedWebsite() {
        stub($this->request)->existAndNonEmpty('return_to')->returns(true);
        stub($this->request)->get('return_to')->returns('http://evil.tld/');
        $this->assertEqual('/my/redirect.php?return_to=/', $this->url_redirect->makeReturnToUrl($this->request, '/my/redirect.php'));
    }

    public function itNotRedirectToUntrustedCode() {
        stub($this->request)->existAndNonEmpty('return_to')->returns(true);
        stub($this->request)->get('return_to')->returns('javascript:alert(1)');
        $this->assertEqual('/my/redirect.php?return_to=/', $this->url_redirect->makeReturnToUrl($this->request, '/my/redirect.php'));
        stub($this->request)->get('return_to')->returns('vbscript:msgbox(1)');
        $this->assertEqual('/my/redirect.php?return_to=/', $this->url_redirect->makeReturnToUrl($this->request, '/my/redirect.php'));
    }

}
