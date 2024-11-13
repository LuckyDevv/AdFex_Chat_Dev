<?php

namespace controllers;

use db_managers\Sessions_DB;

class AutoAuthController extends Controller
{
    private array $params = [];
    public function __construct(array $params) {
        $this->params = $params;
    }

    public function handle(): ?string
    {
        if (isset($this->params['session_id'], $this->params['_usi_g%$_id$$'])) {
            $session_valid = (new Sessions_DB())->validateSession($this->params['session_id'], $this->params['_usi_g%$_id$$']);
            if (is_string($session_valid)) {
                return $session_valid;
            }
        }
        return null;
    }
}