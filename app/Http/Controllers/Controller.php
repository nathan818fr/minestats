<?php

namespace MineStats\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function validateOnly(Request $req, array $rules)
    {
        foreach ($req->all() as $k => $v) {
            if (!isset($rules[$k])) {
                throw new \HttpInvalidParamException('Illegal parameter: '.e($k));
            }
        }
        foreach ($rules as $k => $rule) {
            if ($rule instanceof \Closure) {
                if ($req->get($k) !== null) {
                    $rules[$k] = $rule();
                } else {
                    unset($rules[$k]);
                }
            }
        }
        $this->validate($req, $rules);
    }

    protected function arrayParam(Request $req, $fieldName, $separator = ',')
    {
        $field = $req->get($fieldName);
        if ($field !== null && !is_array($field)) {
            if (empty($field)) {
                $field = [];
            } else {
                $field = explode($separator, $field);
            }
            $req->offsetSet($fieldName, $field);
        }
    }

    protected function parseOrder($paramValue, $defaultValue = null)
    {
        if ($paramValue === null) {
            if ($defaultValue !== null) {
                return $this->parseOrder($defaultValue, null);
            } else {
                return null;
            }
        }

        if (starts_with($paramValue, '-')) {
            return [substr($paramValue, 1), 'desc'];
        }

        return [$paramValue, 'asc'];
    }
}
