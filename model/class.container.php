<?php

	class container {

		function container($args = false) {
			$this->cast($args);
			return $this;
		}

		function json_encode() {
			$copy = $this;
			$unsets = array('warnings', 'errors', 'db');
			foreach($copy as $key => $value) {
				if (is_numeric($key) || in_array($key, $unsets))
					unset($copy->$key);
			}
			return json_encode($copy);
		}

		function cast($args = false) {
			if (is_array($args) || is_object($args)) {
				foreach($args as $key => $value) {
					if (!is_numeric($key)) {
						if (is_string($value)) {
							$this->$key = stripslashes($value);
						} else {
							$this->$key = $value;
						}
					}
				}
			}
			return $this;
		}

		function keys($force = false) {
			if (!isset($this->keys) || $force) {
				$this->keys = array();
				foreach($this as $key => $value) {
					$this->keys[] = $key;
				}
				return $this->keys;
			}
		}

		function __set($property, $value) {
			if ($property == '')
				return false;
			if (method_exists($this, 'set_'.$property)) {
				$method = 'set_'.$property;
				$callers = debug_backtrace();
				if ($callers[1]['function'] != $method) {
					return $this->$method($value);
				}
			}
			$this->$property = $value;
			return $this->$property;
		}

		function __get($property) {
			if (method_exists($this, 'get_'.$property)) {
				$method = 'get_'.$property;
				return $this->$method();
			} else if (isset($this->$property)) {
				return $this->$property;
			}
			return false;
		}

		function destroy() {
			foreach($this as $key => $value) {
				if (is_object($value) && method_exists($value, 'destroy')) {
					$value->destroy();
				}
				unset($this->$key);
			}
			gc_collect_cycles();
		}

	}
