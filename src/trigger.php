<?php

namespace MethodTriggers;

use ArrayUtils\Arrays;

	/* Still need to add 'only' & 'except' clauses */
	trait Trigger {

		private $_beforeAction = null;
		private $_afterAction = null;
		private $_allBeforeActionTriggers = null;
		private $_allAfterActionTriggers = null;

		private function _addAllTrigger($trigger, $method, $except = []) {

			if ($trigger == "_beforeAction") {
				$trigger = "_allBeforeActionTriggers";
			}
			else {
				$trigger = "_allAfterActionTriggers";
			}

			if (!$this->$trigger->exists($method)) {

				if (!is_array($except)) {
					$except = [$except];
				}

				$this->$trigger[$method] = $except;

			}
			else if (!empty($except)) {

				if (!is_array($except)) {
					$this->$trigger[$method][] = $except;
				}
				else {
					$this->$trigger[$method] = array_merge($this->$trigger[$method], $except);
				}

			}

		}

		private function _addTrigger($trigger, $method, $actions) {

			$this->_initializeMethodTriggers();

			if (empty($actions)) {
				$this->_addAllTrigger($trigger, $method);
			}

			foreach ($actions as $action) {

				if (is_array($action)) {

					if (isset($action["except"])) {
						$this->_addAllTrigger($trigger, $method, $action["except"]);
					}
					else {
						$this->_addTrigger($trigger, $method, $action);
					}

					continue;

				}

				if ($this->$trigger->exists($action)) {
					$this->$trigger[$action][] = $method;
				}
				else {
					$this->$trigger[$action] = new Arrays($method);
				}

			}

		}

		function afterAction($trigger) {
			$this->_addTrigger("_afterAction", $trigger, array_slice(func_get_args(), 1));
		}

		function beforeAction($trigger) {
			$this->_addTrigger("_beforeAction", $trigger, array_slice(func_get_args(), 1));
		}

		private function _initializeMethodTriggers() {

			if (is_null($this->_beforeAction)) {
				$this->_beforeAction = new Arrays;
			}
			if (is_null($this->_afterAction)) {
				$this->_afterAction = new Arrays;
			}

			if (is_null($this->_allBeforeActionTriggers)) {
				$this->_allBeforeActionTriggers = new Arrays;
			}
			if (is_null($this->_allAfterActionTriggers)) {
				$this->_allAfterActionTriggers = new Arrays;
			}

		}

		function invokeTriggerFor($action, $before = true) {

			$this->_initializeMethodTriggers();

			$trigger = "_afterAction";
			if ($before) {
				$trigger = "_beforeAction";
			}

			if ($trigger == "_beforeAction") {
				$this->_invokeAllTriggers($this->_allBeforeActionTriggers, $action);
			}

			if ($this->$trigger->exists($action)) {
				$this->_invokeTriggers($this->$trigger[$action]);
			}

			if ($trigger == "_afterAction") {
				$this->_invokeAllTriggers($this->_allAfterActionTriggers, $action);
			}

		}

		private function _invokeAllTriggers($trigger, $action) {

			foreach ($trigger as $method => $except) {

				if (!in_array($action, $except)) {
					$this->$method();
				}

			}

		}

		private function _invokeTriggers($triggers) {

			$t = $this;
			$triggers->map(function ($m) use ($t) {
				$t->$m();
			});

		}

	}

?>