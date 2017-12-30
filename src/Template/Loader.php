<?php

namespace Wenprise\Template;

/**
 * 模板加载和获取模板类型的函数
 */

use Nette\Utils\Arrays;
use Nette\Utils\Finder;

class Loader {

	/**
	 * 自定义模板加载器, 优先加载主题中的模板, 如果主题中的模板不存在, 就加载插件中的
	 *
	 * @param mixed  $slug 模板名称的前缀, 模板名称的后缀
	 * @param string $name (default: '')
	 *
	 * @package template
	 */
	public static function get_part( $slug, $name = '' ) {
		$template = '';

		// 查找主题中定义的插件模板
		if ( $name ) {
			$template = locate_template( [ "{$slug}-{$name}.php", "wizhi/{$slug}-{$name}.php" ] );
		}

		// 加载插件中定义的模板
		if ( ! $template && $name && file_exists( WIZHI_CMS_PATH . "templates/{$slug}-{$name}.php" ) ) {
			$template = WIZHI_CMS_PATH . "templates/{$slug}-{$name}.php";
		}

		// 加载主题中的默认模板
		if ( ! $template ) {
			$template = locate_template( [ "{$slug}.php", "wizhi/{$slug}.php" ] );
		}

		// 允许第三方插件过滤模板文件
		$template = apply_filters( 'wizhi_get_template_part', $template, $slug, $name );

		if ( $template ) {
			load_template( $template, false );
		}
	}


	/**
	 * 获取存档页面模板
	 *
	 * @param string $dir 模板文件所在的目录名称
	 *
	 * @return array
	 */
	public static function get_loop( $dir = "wizhi" ) {
		$template_in_plugin = WIZHI_CMS_PATH . "templates/" . $dir;
		$template_in_theme  = get_template_directory() . "/" . $dir;

		$templates_in_plugin = [];
		$templates_in_theme  = [];


		// 插件中的模板
		if ( is_dir( $template_in_plugin ) ) {
			$finder = Finder::findFiles( '*.php' )
			                ->in( $template_in_plugin );

			foreach ( $finder as $key => $file ) {

				$filename        = $file->getFilename();
				$file_name_array = explode( '-', $filename );
				$name            = Arrays::get( $file_name_array, 1, 'None' );

				$headers = [
					'Name' => __( 'Loop Template Name', 'wizhi' ),
				];

				$file_info = get_file_data( $key, $headers );

				// 获取模板名称
				if ( $file_info[ 'Name' ] ) {
					$option_name = $file_info[ 'Name' ];
				} else {
					$option_name = ucfirst( $name );
				}

				$templates_in_theme[ explode( '.', $name )[ 0 ] ] = $option_name;

			}
		}


		// 主题中的模板
		if ( is_dir( $template_in_theme ) ) {

			$finder = Finder::findFiles( '*.php' )
			                ->in( $template_in_theme );

			foreach ( $finder as $key => $file ) {

				$filename        = $file->getFilename();
				$file_name_array = explode( '-', $filename );
				$name            = Arrays::get( $file_name_array, 1, 'None' );

				$headers = [
					'Name' => __( 'Loop Template Name', 'wizhi' ),
				];

				$file_info = get_file_data( $key, $headers );

				// 获取模板名称
				if ( $file_info[ 'Name' ] ) {
					$option_name = $file_info[ 'Name' ];
				} else {
					$option_name = ucfirst( $name );
				}

				$templates_in_theme[ explode( '.', $name )[ 0 ] ] = $option_name;

			}
		}


		// 合并插件和主题中的模板，优先使用主题中模板
		$templates = wp_parse_args( $templates_in_theme, $templates_in_plugin );

		return $templates;
	}


	/**
	 * 获取模板为变量, 而不是直接显示
	 *
	 * @param  string $slug 模板名称前缀
	 * @param string  $name 模板名称
	 *
	 * @return string
	 */
	public static function load_part( $slug, $name = '' ) {

		ob_start();
		$part = new Template;
		$part->get_part( $slug, $name );
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

}