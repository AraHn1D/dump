<?php
	/**
	 * Custom functional to do dump variables easily and visual
	 *
	 * @author  Yury Marty (AraHnID)
	 * @package dump
	 * @link    https://github.com/AraHn1D/dump/
	 */

	/**
	 * Function prepares object to dump
	 *
	 * @param object $object
	 * @param int    $depth
	 *
	 * @return string
	 */
	function prepare_object_argument( $object, $depth = 1 ) {
		$object_to_check   = (array) $object;
		$class_name        = get_class( $object );
		$parent_class_name = get_parent_class( $object );
		$object_amount     = count( $object_to_check );
		$html_id           = hash( 'sha256', rand( 000000000000000, 999999999999999 ) );
		$code_space        = '   ';

		$output = $object_amount > 0 ? '<span style="cursor: pointer;" data-id="' . $html_id . '" onclick="clickableObjectHandler(this);">' . $class_name . ' ( ' . $object_amount . ' )' : $class_name . ' ( ' . $object_amount . ' )';
		$output .= $object_amount > 0 ? ' <em>{...}</em> {</span>' : ' <em>{}</em>';

		if ( $object_amount > 0 ) {
			$output .= '<span id="' . $html_id . '">';
		}

		foreach ( $object_to_check as $key => $value ) {
			// Seeking for the entry of Class name or asterisk
			$is_own_property     = false;
			$class_name_position = $parent_class_name === false ? false : strpos( $key, $parent_class_name );

			if ( $class_name_position === false ) {
				$is_own_property     = true;
				$class_name_position = strpos( $key, $class_name );
			}

			$asterisk_position = strpos( $key, '*' );

			// Checking key for private or protected
			$is_private   = !$class_name_position ? false : true;
			$is_protected = !$asterisk_position ? false : true;

			$key_additional = is_string( $key ) ? "'" : '';

			if ( $is_private ) {
				$key                 = str_replace( [ $class_name, !$parent_class_name ? $class_name : $parent_class_name ], '', $key );
				$property_visibility = '<span style="color: #92008d;font-style: italic;">private</span> ';
				$property_visibility .= ( $is_own_property ? $class_name : $parent_class_name ) . ' ';
			}
			else if ( $is_protected ) {
				$key                 = str_replace( '*', '', $key );
				$property_visibility = '<span style="color: #92008d;font-style: italic;">protected</span> ';
			}
			else {
				$property_visibility = '<span style="color: #92008d;font-style: italic;">public</span> ';
			}

			$output .= PHP_EOL . str_repeat( $code_space, $depth ) . $property_visibility . '[ ' . $key_additional . '<span style="color: #92008d;">' . $key . '</span>' . $key_additional . ' ] => ' . prepare_simple_argument( $value );

			if ( is_array( $value ) ) {
				$output .= prepare_array_argument( $value, $depth + 1 );
			}

			if ( is_object( $value ) ) {
				$output .= prepare_object_argument( $value, $depth + 1 );
			}
		}

		if ( $object_amount > 0 ) {
			$output .= PHP_EOL . str_repeat( $code_space, $depth - 1 ) . '</span>}';
		}

		return $output;
	}

	/**
	 * Function prepares array to dump
	 *
	 * @param array $array
	 * @param int   $depth
	 *
	 * @return string
	 */
	function prepare_array_argument( $array, $depth = 1 ) {
		$array_amount = count( $array );
		$html_id      = hash( 'sha256', rand( 000000000000000, 999999999999999 ) );
		$code_space   = '   ';

		$output = $array_amount > 0 ? '<span style="cursor: pointer;" data-id="' . $html_id . '" onclick="clickableObjectHandler(this);">' : '';
		$output .= $array_amount > 0 ? '( ' . $array_amount . ' )' : '( ' . $array_amount . ' )';
		$output .= $array_amount > 0 ? ' <em>[...]</em> {</span>' : ' <em>[]</em>';

		if ( $array_amount > 0 ) {
			$output .= '<span id="' . $html_id . '">';
		}

		foreach ( $array as $key => $value ) {
			$key_additional = is_string( $key ) ? "'" : '';

			$output .= PHP_EOL . str_repeat( $code_space, $depth ) . '[ ' . $key_additional . '<span style="color: #92008d;">' . $key . '</span>' . $key_additional . ' ] => ' . prepare_simple_argument( $value );

			if ( is_array( $value ) ) {
				$output .= prepare_array_argument( $value, $depth + 1 );
			}

			if ( is_object( $value ) ) {
				$output .= prepare_object_argument( $value, $depth + 1 );
			}
		}

		if ( $array_amount > 0 ) {
			$output .= PHP_EOL . str_repeat( $code_space, $depth - 1 ) . '</span>}';
		}

		return $output;
	}

	/**
	 * Function prepares simple variables to dump
	 *
	 * @param $argument
	 *
	 * @return string
	 */
	function prepare_simple_argument( $argument ) {
		$output = '';

		switch ( gettype( $argument ) ) {
			case 'boolean':
				$color  = '#92008d';
				$output .= 'bool( <span style="color:' . $color . '">';
				$output .= $argument ? 'true' : 'false';
				$output .= '</span> )';
				break;
			case 'integer':
				$color  = '#ff3f38';
				$output .= 'int( <span style="color:' . $color . '">' . $argument . '</span> )';
				break;
			case 'double':
				$color  = '#14719a';
				$output .= 'float( <span style="color:' . $color . '">' . $argument . '</span> )';
				break;
			case 'string':
				$color  = '#1f6c53';
				$output .= 'string( ' . strlen( $argument ) . ' ) ' . '\'' . '<span style="color:' . $color . '">' . strip_tags( $argument ) . '</span>' . '\'';
				break;
			case 'resource':
				$output .= '{resource}';
				break;
			case 'NULL':
				$color  = '#92008d';
				$output .= '<span style="color:' . $color . '">NULL</span>';
				break;
			case 'unknown type':
				$output .= '{unknown}';
				break;
		}

		return $output;
	}

	/**
	 * Dumps information about the variables and wraps it in <pre tag>
	 *
	 * @param mixed $expression The variable you want to export.
	 * @param mixed $_          [optional]
	 *
	 * @return void
	 */
	function d( $expression, $_ = null ) {
		$arguments = func_get_args();
		$styles    = <<<CSS
		pre.dump {
			font-size   : 12px;
			font-style  : normal;
			font-family : Menlo, Monaco, Consolas, Lucida Console, monospace;
			line-height : 14px;
			padding     : 10px 15px;
			text-align  : left;
			white-space : pre-wrap;
		}
		
		pre.dump span {
			font-style  : normal;
			font-family : Menlo, Monaco, Consolas, Lucida Console, monospace;
		}
		
		pre.dump em {
			font-style  : italic;
			font-family : Menlo, Monaco, Consolas, Lucida Console, monospace;
		}
		
		pre.dump strong {
			font-weight : bold;
			font-family : Menlo, Monaco, Consolas, Lucida Console, monospace;
		}
CSS;

		echo '<pre class="dump">';
		echo '<style>' . $styles . '</style>';

		foreach ( $arguments as $index => $argument ) {
			if ( is_array( $argument ) ) {
				$output = prepare_array_argument( $argument );
			}
			else if ( is_object( $argument ) ) {
				$output = prepare_object_argument( $argument );
			}
			else {
				$output = prepare_simple_argument( $argument );
			}

			echo $output;
			echo $index < count( $arguments ) - 1 ? PHP_EOL : '';
		}

		echo '</pre>';

		$js     = <<<JS
		function clickableObjectHandler( node ) {
			var target = document.getElementById( node.getAttribute( 'data-id' ) );
			target.style.display = target.style.display === 'none' ? '' : 'none';
		}
		
		var thisDumpScript = document.querySelector( 'script[name="dump"]' );
		thisDumpScript.parentNode.removeChild( thisDumpScript );
JS;
		$js_min = <<<JS
		function clickableObjectHandler(e){var t=document.getElementById(e.getAttribute("data-id"));t.style.display="none"===t.style.display?"":"none"}var thisDumpScript=document.querySelector('script[name="dump"]');thisDumpScript.parentNode.removeChild(thisDumpScript);
JS;

		echo '<script name="dump" type="text/javascript">' . $js_min . '</script>';
	}

	/**
	 * Dumps information about the variables, wraps it in <pre tag> and then dies
	 *
	 * @param mixed $expression The variable you want to export.
	 * @param mixed $_          [optional]
	 *
	 * @return void
	 */
	function _d( $expression, $_ = null ) {
		$arguments        = func_get_args();
		$backtrace        = debug_backtrace();
		$backtrace_length = count( $backtrace );

		$pre_backtrace = '<div style="border-left: 2px solid #202023;padding-left: 6px">';

		for ( $index = $backtrace_length - 1, $single_trace = $backtrace[ $index ]; $index >= 0; $single_trace = $backtrace[ --$index ] ) {
			$pre_backtrace .= $backtrace_length - $index . '. ';
			$pre_backtrace .= isset( $single_trace[ 'file' ] ) ? $single_trace[ 'file' ] . ' ' : '';
			$pre_backtrace .= isset( $single_trace[ 'line' ] ) ? '( <strong>' . $single_trace[ 'line' ] . '</strong> ) ' : '';
			$pre_backtrace .= isset( $single_trace[ 'function' ] ) ? 'in <span style="font-style: italic;font-weight: 600;color: #1669aa;">f</span> ' . $single_trace[ 'function' ] : '';

			if ( !empty( $single_trace[ 'args' ] ) ) {
				$caller_argument_types = [];

				foreach ( $single_trace[ 'args' ] as $arg ) {
					array_push( $caller_argument_types, '<span style="font-style: italic;color: #7618aa;">' . gettype( $arg ) . '</span>' );
				}

				$pre_backtrace .= '( ' . implode( ', ', $caller_argument_types ) . ' )';
			}
			else {
				$pre_backtrace .= '()';
			}

			$pre_backtrace .= $index > 0 ? PHP_EOL : '';
		}

		$pre_backtrace .= '</div>';

		$pre_backtrace .= PHP_EOL;

		$styles = <<<CSS
		pre.dump {
			font-size   : 12px;
			font-style  : normal;
			font-family : Menlo, Monaco, Consolas, Lucida Console, monospace;
			line-height : 14px;
			padding     : 10px 15px;
			text-align  : left;
			white-space : pre-wrap;
		}
		
		pre.dump span {
			font-style  : normal;
			font-family : Menlo, Monaco, Consolas, Lucida Console, monospace;
		}
		
		pre.dump em {
			font-style  : italic;
			font-family : Menlo, Monaco, Consolas, Lucida Console, monospace;
		}
		
		pre.dump strong {
			font-weight : bold;
			font-family : Menlo, Monaco, Consolas, Lucida Console, monospace;
		}
CSS;

		echo '<pre class="dump">';
		echo '<style>' . $styles . '</style>';
		echo $pre_backtrace;

		foreach ( $arguments as $index => $argument ) {
			if ( is_array( $argument ) ) {
				$output = prepare_array_argument( $argument );
			}
			else if ( is_object( $argument ) ) {
				$output = prepare_object_argument( $argument );
			}
			else {
				$output = prepare_simple_argument( $argument );
			}

			echo $output;
			echo $index < count( $arguments ) - 1 ? PHP_EOL : '';
		}

		echo '</pre>';

		$js     = <<<JS
		function clickableObjectHandler( node ) {
			var target = document.getElementById( node.getAttribute( 'data-id' ) );
			target.style.display = target.style.display === 'none' ? '' : 'none';
		}
		
		var thisDumpScript = document.querySelector( 'script[name="dump"]' );
		thisDumpScript.parentNode.removeChild( thisDumpScript );
JS;
		$js_min = <<<JS
		function clickableObjectHandler(e){var t=document.getElementById(e.getAttribute("data-id"));t.style.display="none"===t.style.display?"":"none"}var thisDumpScript=document.querySelector('script[name="dump"]');thisDumpScript.parentNode.removeChild(thisDumpScript);
JS;

		echo '<script name="dump" type="text/javascript">' . $js_min . '</script>';

		die( 200 );
	}
