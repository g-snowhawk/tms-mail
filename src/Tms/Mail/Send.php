<?php
/**
 * This file is part of Tak-Me System.
 *
 * Copyright (c)2016 PlusFive (https://www.plus-5.com)
 *
 * This software is released under the MIT License.
 * https://www.plus-5.com/licenses/mit-license
 */

namespace Tms\Mail;

use P5\Environment;
use P5\Mail;

/**
 * Entry management request response class.
 *
 * @license https://www.plus-5.com/licenses/mit-license  MIT License
 * @author  Taka Goto <www.plus-5.com>
 */
class Send extends \Tms\Mail
{
    const DEFAULT_TEMPLATE = 'mail/send.tpl';
    const SEND_TEMPLATE = 'mail/sendmail.tpl';
    const REPLY_TEMPLATE = 'mail/reply.tpl';

    /**
     * Object Constructor.
     */
    public function __construct()
    {
        $params = func_get_args();
        call_user_func_array('parent::__construct', $params);

        $this->view->bind(
            'header',
            ['title' => '送信完了', 'id' => 'tms-mail-send', 'class' => 'tms-mail']
        );
    }

    /**
     * Default view.
     */
    public function init()
    {
        $this->app->execPlugin('beforeInit');

        $template = self::DEFAULT_TEMPLATE;
        $post = $this->request->post();
        $this->view->bind('post', $post);

        $noform = false;
        if (!empty($post['prev'])) {
            $template = Form::DEFAULT_TEMPLATE;
            $this->view->bind(
                'header',
                ['title' => 'メールフォーム', 'id' => 'tms-mail-form', 'class' => 'tms-mail']
            );
        } else {
            if (false === $this->sendmail() || false === $this->reply()) {
                $template = Confirm::DEFAULT_TEMPLATE;
                $this->view->bind(
                    'header',
                    ['title' => '入力内容確認', 'id' => 'tms-mail-confirm', 'class' => 'tms-mail']
                );
            } else {
                $this->session->clear('ticket');
                $noform = true;
            }
        }

        if (false === $noform) {
            $form = $this->view->param('form');
            $form['action'] = parse_url(Environment::server('request_uri'), PHP_URL_PATH);
            $this->view->bind('form', $form);
        }

        $this->view->render($template);
    }

    /**
     * Sending mail.
     *
     * @return bool
     */
    private function sendmail()
    {
        $from = $this->request->param('email');
        $envfrom = $from;
        $to = $this->app->cnf('mail:mail_to');
        $cc = $this->app->cnf('mail:mail_cc');
        $bcc = $this->app->cnf('mail:mail_bcc');
        $subject = $this->request->param('s1_subject');
        if (empty($subject)) {
            $subject = $this->app->cnf('mail:mail_subject');
        }

        $view = clone $this->view;
        $message = $view->render(self::SEND_TEMPLATE, true);

        return $this->mail($from, $to, $subject, $message, $bcc, $cc, $envfrom);
    }

    /**
     * Auto reply.
     *
     * @return bool
     */
    private function reply()
    {
        $replyfrom = $this->app->cnf('mail:reply_from');
        if (empty($replyfrom)) {
            return true;
        }
        $from = $replyfrom;
        $to = $this->request->param('email');
        $bcc = '';
        $cc = '';
        $subject = $this->app->cnf('mail:reply_subject');

        $view = clone $this->view;
        $message = $view->render(self::REPLY_TEMPLATE, true);

        return $this->mail($from, $to, $subject, $message, $bcc, $cc);
    }

    /**
     * Execute send mail.
     *
     * @param string $from
     * @param string $to
     * @param string $subject
     * @param string $message
     * @param string $bcc
     * @param string $cc
     * @param string $envfrom
     *
     * @return bool
     */
    private function mail($from, $to, $subject, $message, $bcc = '', $cc = '', $envfrom = '')
    {
        $host = $this->app->cnf('mail:smtp_host');
        if (is_null($host)) {
            $host = 'localhost';
        }
        $port = $this->app->cnf('mail:smtp_port');
        if (is_null($port)) {
            $port = '';
        }
        $user = $this->app->cnf('mail:smtp_user');
        if (is_null($user)) {
            $user = '';
        }
        $pass = $this->app->cnf('mail:smtp_pass');
        if (is_null($pass)) {
            $pass = '';
        }
        $mail = new Mail($host, $port, $user, $pass);

        $mail->from($from);
        $mail->to($to);

        if (!empty($bcc)) {
            $mail->bcc($bcc);
        }
        if (!empty($cc)) {
            $mail->cc($cc);
        }
        if (!empty($envfrom)) {
            $mail->envfrom($envfrom);
        }

        $mail->subject($subject);
        $mail->message($message);

        return $mail->send();
    }
}
