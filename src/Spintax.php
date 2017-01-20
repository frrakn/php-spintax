<?php

namespace Spintax;

mt_srand(time());

class Spintax {
	public $spintaxExpr;

	public function __construct( $expr ) {
		$this->spintaxExpr = $this->compile( $expr );
	}

	public function spin() {
		return spin_expr_arr($this->spintaxExpr);
	}

	private function compile( $expr ) {
		$exprStack = array();
		$currentExpr = array();

		$optionsStack = array();
		$currentOptions = array();

		$tokens = $this->tokenize($expr);
		foreach($tokens as $token) {
			switch($token) {
			case "{":
				array_push($exprStack, $currentExpr);
				array_push($optionsStack, $currentOptions);
				$currentExpr = array();
				$currentOptions = array();
				break;
			case "}":
				array_push($currentOptions, $currentExpr);
				$prevExpr = array_pop($exprStack);
				if (is_null($prevExpr)) {
					throw new \Exception("compile fail: syntax error");
				}
				array_push($prevExpr, new Spinner($currentOptions));
				$currentExpr = $prevExpr;
				$currentOptions = array_pop($optionsStack);
				break;
			case "|":
				array_push($currentOptions, $currentExpr);
				$currentExpr = array();
				break;
			default:
				array_push($currentExpr, $token);
				break;
			}
		}

		if (!empty($exprStack)) {
			throw new \Exception("compile fail: syntax error");
		}

		return $currentExpr;
	}

	private function tokenize( $expr ) {
		$tokens = array();

		$currentExpr = "";
		for($i = 0; $i < strlen($expr); $i++) {
			switch($expr[$i]) {
			case "\\":
				$i++;
				if ($i < strlen($expr)) {
					$currentExpr = $currentExpr . $expr[$i];
				}
				break;
			case "{":
			case "}":
			case "|":
				if ($currentExpr) {
					array_push($tokens, $currentExpr);
				}
				array_push($tokens, $expr[$i]);
				$currentExpr = "";
				break;
			default:
				$currentExpr = $currentExpr . $expr[$i];
				break;
			}
		}
		if ($currentExpr) {
			array_push($tokens, $currentExpr);
		}

		return $tokens;
	}
}

class Spinner {
	public $options;

	public function __construct( $options ) {
		$this->options = $options;
	}

	public function spin() {
		$index = mt_rand(0, count($this->options) - 1);
		$res = $this->options[$index];

		return spin_expr_arr($res);
	}
}

function spin_expr_arr($exprs) {
		$output = "";
		foreach($exprs as $expr) {
			if ($expr instanceof Spinner) {
				$output = $output . $expr->spin();
			} else {
				$output = $output . $expr;
			}
		}

		return $output;
}

$test = new Spintax("{hell{o, wo| }}{rld}");
print $test->spin();
