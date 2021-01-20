<?php
/**
 * This file is part of Tak-Me System.
 *
 * Copyright (c)2016-2017 PlusFive (https://www.plus-5.com)
 *
 * This software is released under the MIT License.
 * https://www.plus-5.com/licenses/mit-license
 */

namespace Tms\Mail;

use P5\Environment;

/**
 * Entry management request response class.
 *
 * @license https://www.plus-5.com/licenses/mit-license  MIT License
 * @author  Taka Goto <www.plus-5.com>
 */
class Form extends \Tms\Mail
{
    const DEFAULT_TEMPLATE = 'mail/form.tpl';

    /**
     * Object Constructor.
     */
    public function __construct()
    {
        $params = func_get_args();
        call_user_func_array('parent::__construct', $params);

        $this->view->bind(
            'header',
            ['title' => 'メールフォーム', 'id' => 'tms-mail-form', 'class' => 'tms-mail']
        );
    }

    /**
     * Default view.
     *
     * @return void
     */
    public function init()
    {
        $this->app->execPlugin('beforeInit');

        $form = $this->view->param('form');
        $form['action'] = parse_url(Environment::server('request_uri'), PHP_URL_PATH);
        $this->view->bind('form', $form);

        $this->view->bind('err', $this->app->err);
        $this->view->render(self::DEFAULT_TEMPLATE);
    }
}
