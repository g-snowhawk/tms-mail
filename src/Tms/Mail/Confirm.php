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
class Confirm extends \Tms\Mail
{
    const DEFAULT_TEMPLATE = 'mail/confirm.tpl';

    /**
     * Object Constructor.
     */
    public function __construct()
    {
        $params = func_get_args();
        call_user_func_array('parent::__construct', $params);

        $this->view->bind(
            'header',
            ['title' => '入力内容確認', 'id' => 'tms-mail-confirm', 'class' => 'tms-mail']
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

        $template = self::DEFAULT_TEMPLATE;

        if (false === $this->validation()) {
            $template = Form::DEFAULT_TEMPLATE;
        }

        $form = $this->view->param('form');
        $form['action'] = parse_url(Environment::server('request_uri'), PHP_URL_PATH);
        $this->view->bind('form', $form);

        $post = $this->request->post();
        $this->view->bind('post', $post);

        $this->view->bind('err', $this->app->err);
        $this->view->render($template);
    }

    /**
     * Post data validation.
     *
     * @reutrn bool
     */
    private function validation()
    {
        $valid = [];
        if (false !== stream_resolve_include_path($this->validation_map)) {
            include_once $this->validation_map;
        }

        return $this->validate($valid);
    }
}
