<?php

namespace MethodTriggers;

use ArrayUtils\Arrays;

	/* Still need to add 'only' & 'except' clauses */
	trait Trigger {

		private $_beforeAction = null;
		private $_afterAction = null;

		private function _addTrigger($trigger, $method, $actions) {

			$this->_initializeMethodTriggers();

			if (empty($actions)) {
				$actions = ["all"];
			}

			foreach ($actions as $action) {

				if (is_array($action)) {

					$this->_addTrigger($trigger, $method, $action);
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

		}

		function invokeTriggerFor($action, $before = true) {

			$this->_initializeMethodTriggers();

			$trigger = "_afterAction";
			if ($before) {
				$trigger = "_beforeAction";
			}

			if ($this->$trigger->exists($action)) {
				$this->_invokeTriggers($this->$trigger[$action]);
			}

			if ($this->$trigger->exists("all")) {
				$this->_invokeTriggers($this->$trigger["all"]);
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