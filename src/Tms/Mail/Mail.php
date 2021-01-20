<?php
/**
 * This file is part of Tak-Me System.
 *
 * Copyright (c)2016-2017 PlusFive (https://www.plus-5.com)
 *
 * This software is released under the MIT License.
 * https://www.plus-5.com/licenses/mit-license
 */

namespace Tms;

/**
 * Common methods for Tak-Me System.
 *
 * @license https://www.plus-5.com/licenses/mit-license  MIT License
 * @author  Taka Goto <www.plus-5.com>
 */
abstract class Mail extends User implements PackageInterface
{
    /**
     * Application default mode.
     */
    const DEFAULT_MODE = 'mail.form';

    /**
     * Application.
     *
     * @var Tms_App
     */
    public $app;

    /**
     * Template file name.
     *
     * @var string
     */
    public $template_file = 'default.html.twig';

    /**
     * Custom validation settings path.
     *
     * @var string
     */
    protected $validation_map = 'validate.php';

    /**
     * Object Constructer.
     */
    public function __construct()
    {
        $params = func_get_args();
        foreach ($params as $param) {
            if (is_object($param) && (get_class($param) === 'Tms\\App' || is_subclass_of($param, 'Tms\\App'))) {
                $this->app = $param;
            }
        }
        $this->view->bind('apps', $this);

        $this->useExtendedTemplate();
    }

    /**
     * check setting variables.
     *
     * @return bool
     */
    public function __isset($name)
    {
        return property_exists($this, $name);
    }

    /**
     * Getter method.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        switch ($name) {
            case 'db': return $this->app->db;
            case 'env': return $this->app->env;
            case 'request': return $this->app->request;
            case 'session': return $this->app->session;
            case 'view': return $this->app->view;
        }
        if (false === property_exists($this, $name)) {
            if ($this->app->cnf('global:debugmode') === '1') {
                trigger_error("property `$name` does not exists.", E_USER_ERROR);
            }

            return;
        }

        return $this->$name;
    }

    /**
     * Return to package name.
     *
     * The return value doesn't include namespace
     *
     * @final
     *
     * @return string The return value must be lowercase
     */
    final public static function packageName()
    {
        return strtolower(stripslashes(str_replace(__NAMESPACE__, '', __CLASS__)));
    }

    final public static function applicationName()
    {
        return 'Mail';
    }

    final public static function applicationLabel()
    {
        return 'Mail Form';
    }

    /**
     * This package version
     *
     * @final
     *
     * @return string
     */
    final public static function version()
    {
        return System::getVersion(__CLASS__);
    }

    /**
     * Template directory
     *
     * @final
     *
     * @return string|null
     */
    final public static function templateDir()
    {
        $dir = (is_dir('./' . \Tms\View::TEMPLATE_DIR_NAME)) ? '.' : __DIR__;
        return $dir . '/' . \Tms\View::TEMPLATE_DIR_NAME;
    }

    /**
     * Must be implements this method
     */
    public static function unload()
    {
    }

    /**
     * Modify HTML ID.
     *
     * @param $id
     */
    public function setHtmlId($id)
    {
        $globals = $this->view->param();
        $header = $globals['header'];
        $header['id'] = $id;
        $this->view->bind('header', $header);
    }

    /**
     * Validation form data.
     *
     * @param array $valid
     *
     * @return bool
     */
    public function validate($valid)
    {
        $result = true;
        $v = new \Tms\Validator($valid);
        $err = $v->valid($this->request->param(), $this->request->files());
        foreach ($err as $key => $value) {
            if ($value === 1) {
                $result = false;
            }
            $this->app->err[$key] = $value;
        }

        return $result;
    }

    /**
     * Create save data.
     *
     * @param string $table_name
     * @param array  $post
     * @param array  $skip
     *
     * @return array
     */
    protected function createSaveData($table_name, array $post, array $skip, $cast_string = NULL)
    {
        $data = [];
        $fields = $this->db->getFields($this->db->TABLE($table_name));
        foreach ($fields as $field) {
            if (in_array($field, $skip)) {
                continue;
            }
            if (!isset($post[$field])) {
                continue;
            }
            $data[$field] = $post[$field];
        }

        return $data;
    }
}
