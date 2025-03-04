<?php
/**
 * Frontend blocks dynamic css utils
 *
 * @package Kenta Blocks
 */

namespace KentaBlocks;

final class Css {

	/**
	 * Css initial value
	 */
	const INITIAL_VALUE = '__INITIAL_VALUE__';
	/**
	 * Member Variable
	 *
	 * @var Css
	 */
	private static $instance;
	/**
	 * Breakpoints for media query
	 *
	 * @var array|mixed
	 */
	private $breakpoints;

	/**
	 * @param array $breakpoints
	 */
	private function __construct( array $breakpoints = [] ) {
		$this->setBreakpoints( $breakpoints );
	}

	/**
	 * Set responsive breakpoints
	 *
	 * @param array $breakpoints
	 */
	public function setBreakpoints( $breakpoints = [] ) {
		$this->breakpoints = wp_parse_args( $breakpoints, [
			'desktop' => '1140px',
			'tablet'  => '1024px',
			'mobile'  => '768px',
		] );
	}

	/**
	 *  Initiator
	 */
	public static function get_instance( array $breakpoints = [] ) {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self( $breakpoints );
		}

		return self::$instance;
	}

	/**
	 * Generate sidebar dynamic css
	 *
	 * @return string
	 */
	public function dynamicSidebarCssRaw() {
		return $this->parse( array_merge( $this->vars(), $this->dynamicCss( kb_sidebar_blocks() ) ) );
	}

	/**
	 * Parse css output
	 *
	 * @param array $css_output
	 * @param bool $beauty
	 *
	 * @return string Generated CSS.
	 */
	public function parse( $css_output = [], $beauty = false ) {

		$parse_css     = '';
		$tablet_output = [];
		$mobile_output = [];
		$eol           = $beauty ? PHP_EOL : '';

		if ( ! is_array( $css_output ) || count( $css_output ) <= 0 ) {
			return $parse_css;
		}

		foreach ( $css_output as $selector => $properties ) {

			if ( null === $properties ) {
				break;
			}

			if ( ! count( $properties ) ) {
				continue;
			}

			$temp_parse_css     = $selector . '{' . $eol;
			$temp_tablet_output = [];
			$temp_mobile_output = [];
			$properties_added   = 0;

			foreach ( $properties as $property => $value ) {

				// responsive value
				if ( is_array( $value ) ) {
					$temp_tablet_output[ $property ] = $value['tablet'] ?? '';
					$temp_mobile_output[ $property ] = $value['mobile'] ?? '';

					$value = $value['desktop'] ?? '';
				}

				if ( '' === $value || null === $value || self::INITIAL_VALUE === $value || is_array( $value ) ) {
					continue;
				}

				$properties_added ++;

				$temp_parse_css .= $property . ':' . $value . ';' . $eol;
			}

			$temp_parse_css .= '}';

			if ( ! empty( $temp_tablet_output ) ) {
				$tablet_output[ $selector ] = $temp_tablet_output;
			}

			if ( ! empty( $temp_mobile_output ) ) {
				$mobile_output[ $selector ] = $temp_mobile_output;
			}

			if ( $properties_added > 0 ) {
				$parse_css .= $temp_parse_css;
			}
		}

		$tablet_css = $this->parse( $tablet_output, $beauty );
		if ( $tablet_css !== '' && isset( $this->breakpoints['tablet'] ) ) {
			$tablet_css = '@media (max-width: ' . $this->breakpoints['tablet'] . ') {' . $eol . $tablet_css . $eol . '}' . $eol;
		}

		$mobile_css = $this->parse( $mobile_output, $beauty );
		if ( $mobile_css !== '' && isset( $this->breakpoints['desktop'] ) ) {
			$mobile_css = '@media (max-width: ' . $this->breakpoints['mobile'] . ') {' . $eol . $mobile_css . $eol . '}' . $eol;
		}

		return $parse_css . $tablet_css . $mobile_css;
	}

	/**
	 * Generate css vars
	 *
	 * @return array[]|\string[][]
	 */
	public function vars() {
		$vars = array();

		if (
			strtolower( kenta_blocks_current_template() ) === 'kenta'
			&&
			kenta_blocks_setting()->value( 'kb_sync_kenta_theme' ) === 'yes'
		) {
			$vars = array(
				'--kb-primary-color'  => 'var(--kenta-primary-color)',
				'--kb-primary-active' => 'var(--kenta-primary-active)',
				'--kb-accent-color'   => 'var(--kenta-accent-color)',
				'--kb-accent-active'  => 'var(--kenta-accent-active)',
				'--kb-base-300'       => 'var(--kenta-base-300)',
				'--kb-base-200'       => 'var(--kenta-base-200)',
				'--kb-base-100'       => 'var(--kenta-base-100)',
				'--kb-base-color'     => 'var(--kenta-base-color)',
			);
		} else {
			$vars = array_merge(
				kenta_blocks_css()->colors( kenta_blocks_setting()->value( 'kb_primary_color' ), array(
					'default' => '--kb-primary-color',
					'active'  => '--kb-primary-active',
				) ),
				kenta_blocks_css()->colors( kenta_blocks_setting()->value( 'kb_accent_color' ), array(
					'default' => '--kb-accent-color',
					'active'  => '--kb-accent-active',
				) ),
				kenta_blocks_css()->colors( kenta_blocks_setting()->value( 'kb_base_color' ), array(
					'300'     => '--kb-base-300',
					'200'     => '--kb-base-200',
					'100'     => '--kb-base-100',
					'default' => '--kb-base-color',
				) )
			);
		}

		return array( ':root' => $vars );
	}

	/**
	 * Convert color control value to css output
	 *
	 * @param $colors
	 * @param $maps
	 * @param array $css
	 *
	 * @return array
	 */
	public function colors( $colors, $maps, $css = [] ) {
		if ( $colors === null ) {
			return $css;
		}

		foreach ( $maps as $color => $key ) {
			if ( isset( $colors[ $color ] ) && $colors[ $color ] !== self::INITIAL_VALUE ) {
				if ( ! is_array( $key ) ) {
					$key = [ $key ];
				}

				foreach ( $key as $item ) {
					$css[ $item ] = $colors[ $color ];
				}
			}
		}

		return $css;
	}

	/**
	 * Generate blocks dynamic css
	 *
	 * @param $blocks
	 * @param array $css
	 *
	 * @return array
	 */
	public function dynamicCss( $blocks, $css = array() ) {

		foreach ( $blocks as $block ) {
			if ( is_array( $block ) ) {

				$name = $block['blockName'];

				if ( '' === $name ) {
					continue;
				}

				if ( 'core/block' === $name ) {
					$id = ( isset( $block['attrs']['ref'] ) ) ? $block['attrs']['ref'] : 0;

					if ( $id ) {
						$content = get_post_field( 'post_content', $id );

						$reusable_blocks = kenta_blocks_parse_content( $content );

						$css = $this->dynamicCss( $reusable_blocks, $css );

					}
				} else {
					$kenta_blocks = kenta_blocks_all();
					if ( isset( $kenta_blocks[ $name ]['css'] ) ) {

						if ( isset( $block['attrs']['blockID'] ) ) {
							$css = $kenta_blocks[ $name ]['css']( $block, $css );
						}
					}
				}

				if ( isset( $block['innerBlocks'] ) ) {
					$css = $this->dynamicCss( $block['innerBlocks'], $css );
				}
			}
		}

		return $css;
	}

	/**
	 * Generate blocks dynamic css
	 *
	 * @return string
	 */
	public function dynamicCssRaw( $post = null ) {
		return $this->parse( $this->dynamicCss( kb_post_blocks( $post ) ) );
	}

	public function desktop() {
		return $this->breakpoints['desktop'] ?? '';
	}

	public function tablet() {
		return $this->breakpoints['tablet'] ?? '';
	}

	public function mobile() {
		return $this->breakpoints['mobile'] ?? '';
	}

	/**
	 * Convert spacing control value to css output
	 *
	 * @param mixed $value
	 * @param string $selector
	 *
	 * @return array
	 */
	public function dimensions( $value, $selector = 'margin' ) {

		if ( $value === self::INITIAL_VALUE || $value === null ) {
			return array();
		}

		if ( ! isset( $value['desktop'] ) ) {
			$value = [ null => $value ];
		}

		$spacingCss = [];

		foreach ( $value as $device => $data ) {
			$top    = $data['top'] ?? '';
			$right  = $data['right'] ?? '';
			$bottom = $data['bottom'] ?? '';
			$left   = $data['left'] ?? '';

			if ( $top === '' || $right === '' || $bottom === '' || $left === '' ) {
				continue;
			}

			$spacingCss[ $selector ] = $this->getResponsiveValue(
				"$top $right $bottom $left", $device, $spacingCss[ $selector ] ?? null
			);
		}

		return $spacingCss;
	}

	/**
	 * Get value for responsive
	 *
	 * @param $value
	 * @param null $device
	 * @param null $previous
	 *
	 * @return array|mixed|null
	 */
	protected function getResponsiveValue( $value, $device = null, $previous = null ) {

		if ( ! $device ) {
			return $value;
		}

		$value = [
			$device => $value
		];

		return is_array( $previous ) ? array_merge( $previous, $value ) : $value;
	}

	/**
	 * Convert background control value to css output
	 *
	 * @param array $background
	 *
	 * @return array
	 */
	public function background( $background ) {
		if ( $background === self::INITIAL_VALUE || $background === null ) {
			return [];
		}

		if ( ! isset( $background['desktop'] ) ) {
			$background = [ null => $background ];
		}

		$backgroundCss = [];

		foreach ( $background as $device => $data ) {

			if ( $data['type'] === 'color' ) {
				if ( ( $data['color'] ?? '' ) === 'inherit' ) {
					continue;
				}

				// solid color type
				$backgroundCss['background-color'] = $this->getResponsiveValue(
					$data['color'] ?? '', $device,
					$backgroundCss['background-color'] ?? null
				);
				// override background image
				$backgroundCss['background-image'] = $this->getResponsiveValue(
					'none', $device,
					$backgroundCss['background-image'] ?? null
				);
			} else if ( $data['type'] === 'gradient' ) {
				// gradient type
				$backgroundCss['background-image'] = $this->getResponsiveValue(
					$data['gradient'] ?? '', $device,
					$backgroundCss['background-image'] ?? null
				);
			} else if ( $data['type'] === 'image' ) {
				// background image
				$image = $data['image'] ?? [];

				if ( isset( $image['color'] ) ) {
					$backgroundCss['background-color'] = $this->getResponsiveValue(
						$image['color'], $device, $backgroundCss['background-color'] ?? null
					);
				}
				if ( isset( $image['size'] ) ) {
					$backgroundCss['background-size'] = $this->getResponsiveValue(
						$image['size'], $device, $backgroundCss['background-size'] ?? null
					);
				}
				if ( isset( $image['repeat'] ) ) {
					$backgroundCss['background-repeat'] = $this->getResponsiveValue(
						$image['repeat'], $device, $backgroundCss['background-repeat'] ?? null
					);
				}
				if ( isset( $image['attachment'] ) ) {
					$backgroundCss['background-attachment'] = $this->getResponsiveValue(
						$image['attachment'], $device, $backgroundCss['background-attachment'] ?? null
					);
				}

				if ( isset( $image['source'] ) && isset( $image['source']['url'] ) ) {

					$backgroundCss['background-image'] = $this->getResponsiveValue(
						'url(' . $image['source']['url'] . ')', $device,
						$backgroundCss['background-image'] ?? null
					);

					if ( isset( $image['source']['x'] ) && isset( $image['source']['y'] ) ) {
						$x = $image['source']['x'] * 100;
						$y = $image['source']['y'] * 100;

						$backgroundCss['background-position'] = $this->getResponsiveValue(
							"$x% $y%", $device, $backgroundCss['background-position'] ?? null
						);
					}
				}
			}
		}

		return $backgroundCss;
	}

	/**
	 * Convert border control to css output
	 *
	 * @param $selector
	 * @param array $border
	 *
	 * @return array
	 */
	public function border( $border, $selector = 'border' ) {
		if ( $border === null || $border === self::INITIAL_VALUE ) {
			return array();
		}

		if ( ! isset( $border['desktop'] ) ) {
			$border = [ null => $border ];
		}

		$borderCss = [];

		foreach ( $border as $device => $data ) {
			$value = 'none';
			$style = $data['style'] ?? 'none';
			$width = ( $data['width'] ?? '0' ) . 'px';
			$color = $data['color'] ?? '';
			$hover = $data['hover'] ?? '';

			if ( ( $data['inherit'] ?? false ) || $style === self::INITIAL_VALUE ) {
				continue;
			}

			if ( $style !== 'none' ) {
				$value = "$width $style var(--kb-border-$selector-initial-color)";
			}

			$borderCss[ $selector ] = $this->getResponsiveValue(
				$value, $device, $borderCss[ $selector ] ?? null
			);

			$borderCss['--kb-border-initial-color'] = $this->getResponsiveValue(
				$color, $device, $borderCss['--kb-border-initial-color'] ?? null
			);

			$borderCss["--kb-border-$selector-initial-color"] = $this->getResponsiveValue(
				$color, $device, $borderCss["--kb-border-$selector-initial-color"] ?? null
			);

			$borderCss['--kb-border-hover-color'] = $this->getResponsiveValue(
				$hover, $device, $borderCss['--kb-border-hover-color'] ?? null
			);

			$borderCss["--kb-border-$selector-hover-color"] = $this->getResponsiveValue(
				$hover, $device, $borderCss["--kb-border-$selector-hover-color"] ?? null
			);
		}

		return $borderCss;
	}

	/**
	 * Convert filter control value to css output
	 *
	 * @param mixed $filter
	 *
	 * @return array
	 */
	public function filter( $filter ) {

		if ( $filter === null ) {
			return array();
		}

		if ( ! isset( $filter['desktop'] ) ) {
			$filter = [ null => $filter ];
		}

		$filterCss = [];

		foreach ( $filter as $device => $data ) {
			$value      = null;
			$enable     = ( $data['enable'] ?? '' ) === 'yes';
			$blur       = $data['blur'] ?? 0;
			$contrast   = $data['contrast'] ?? 100;
			$brightness = $data['brightness'] ?? 100;
			$saturate   = $data['saturate'] ?? 100;
			$hue        = $data['hue'] ?? 0;

			if ( $enable ) {
				$value = "brightness( {$brightness}% ) contrast( {$contrast}% ) saturate( {$saturate}% ) blur( {$blur}px ) hue-rotate( {$hue}deg )";
			}

			$filterCss['filter'] = $this->getResponsiveValue(
				$value, $device, $filterCss['filter'] ?? null
			);
		}

		return $filterCss;
	}

	/**
	 * Convert shadow control value to css output
	 *
	 * @param mixed $shadow
	 * @param string $selector
	 *
	 * @return array
	 */
	public function shadow( $shadow, $selector = 'box-shadow' ) {

		if ( $shadow === self::INITIAL_VALUE || $shadow === null ) {
			return array();
		}

		if ( ! isset( $shadow['desktop'] ) ) {
			$shadow = [ null => $shadow ];
		}

		$shadowCss = [];

		foreach ( $shadow as $device => $data ) {
			$value  = 'none';
			$enable = ( $data['enable'] ?? '' ) === 'yes';
			$h      = $data['horizontal'] ?? '0';
			$v      = $data['vertical'] ?? '0';
			$blur   = $data['blur'] ?? '0';
			$spread = $data['spread'] ?? '0';
			$color  = $data['color'] ?? '';
			$inset  = ( $data['inset'] ?? '' ) === 'yes' ? 'inset' : '';

			if ( $enable ) {
				$value = "$color $h $v $blur $spread $inset";
			}

			$shadowCss[ $selector ] = $this->getResponsiveValue(
				$value, $device, $shadowCss[ $selector ] ?? null
			);
		}

		return $shadowCss;
	}

	/**
	 * Convert Text Shadow value to css output
	 *
	 * @param $shadow
	 * @param string $selector
	 *
	 * @return array
	 */
	public function textShadow( $shadow, $selector = 'text-shadow' ) {

		if ( $shadow === self::INITIAL_VALUE || $shadow === null ) {
			return array();
		}

		if ( ! isset( $shadow['desktop'] ) ) {
			$shadow = [ null => $shadow ];
		}

		$shadowCss = [];

		foreach ( $shadow as $device => $data ) {
			$value  = 'none';
			$enable = ( $data['enable'] ?? '' ) === 'yes';
			$h      = $data['hShadow'] ?? '0';
			$v      = $data['vShadow'] ?? '0';
			$blur   = $data['blur'] ?? '0';
			$color  = $data['color'] ?? '';

			if ( $enable ) {
				$value = "$h $v $blur $color";
			}

			$shadowCss[ $selector ] = $this->getResponsiveValue(
				$value, $device, $shadowCss[ $selector ] ?? null
			);
		}

		return $shadowCss;
	}

	/**
	 * Convert typography control value to css output
	 *
	 * @param array $typography
	 *
	 * @return array
	 */
	public function typography( $typography ) {

		if ( $typography === null ) {
			return array();
		}

		Fonts::enqueue( $typography );

		$system = Fonts::system();
		$google = Fonts::google();

		$family        = $typography['family'] ?? 'inherit';
		$variant       = $typography['variant'] ?? '400';
		$fontSize      = $typography['fontSize'] ?? '';
		$lineHeight    = $typography['lineHeight'] ?? '';
		$letterSpacing = $typography['letterSpacing'] ?? '';

		if ( isset( $system[ $family ] ) ) {
			if ( isset( $system[ $family ]['s'] ) && ! empty( $system[ $family ]['s'] ) ) {
				$family = $system[ $family ]['s'];
			}
		}

		if ( isset( $google[ $family ] ) ) {
			$variants = $google[ $family ]['v'] ?? [];
			$family   = $google[ $family ]['f'] ?? $family;
			$variant  = in_array( $variant, $variants ) ? $variant : ( $variants[0] ?? '400' );
		}

		$variant       = $variant === self::INITIAL_VALUE ? '' : $variant;
		$family        = $family === self::INITIAL_VALUE ? '' : $family;
		$fontSize      = $fontSize === self::INITIAL_VALUE ? '' : $fontSize;
		$lineHeight    = $lineHeight === self::INITIAL_VALUE ? '' : $lineHeight;
		$letterSpacing = $letterSpacing === self::INITIAL_VALUE ? '' : $letterSpacing;

		return [
			'font-family'     => $family,
			'font-weight'     => $variant,
			'font-size'       => $fontSize,
			'line-height'     => $lineHeight,
			'letter-spacing'  => $letterSpacing,
			'text-transform'  => $typography['textTransform'] ?? '',
			'text-decoration' => $typography['textDecoration'] ?? '',
		];
	}
}
