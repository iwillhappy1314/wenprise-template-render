<?php

/**
 * 模板加载和获取模板类型的函数
 */
if ( ! function_exists( 'wprs_render_template' ) ) {
	/**
	 * 自定义模板加载器, 优先加载主题中的模板, 如果主题中的模板不存在, 就加载插件中的
	 *
	 * @param mixed  $slug         模板名称的前缀, 模板名称的后缀
	 * @param string $name         模版名称
	 * @param array  $args         渲染模版时的附加参数
	 * @param string $default_path 插件中自定义模版位置的完整路径
	 * @param bool   $echo         直接输入还是返回渲染后的字符串
	 *
	 * @package template
	 *
	 * @return mixed
	 */
	function wprs_render_template( $slug, $name = '', $args = [], $default_path = '', $echo = true ) {

		global $posts, $post, $wp_did_header, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;

		$name = (string) $name;

		// 如果第二个参数是数组，说明没有设置 $name, $name 的位置为 $args, 交换参数位置,
		if ( is_array( $name ) ) {
			$args = $name;
			$default_path = $args;
			$echo = $default_path;
			$name = null;
		}

		$args = apply_filters( "render_template_part_{$slug}_args", $args, $slug, $name, $default_path, $echo );

		/**
		 * 设置模版数据
		 */
		// 解压查询变量
		if ( is_array( $wp_query->query_vars ) ) {
			extract( $wp_query->query_vars, EXTR_SKIP );
		}

		// 检查查询变量
		if ( isset( $s ) ) {
			$s = esc_attr( $s );
		}

		// 解压 $args 参数里面的参数
		if ( is_array( $args ) ) {
			extract( $args, EXTR_SKIP );
		}

		// 如果设置了 $query 参数，我们需要设置 $wp_query 对象 $query 我们需要设置 $wp_query 变量
		if ( isset( $query ) ) {
			$wp_query = $query;
		}

		// 如果设置了 $post_object 变量, 我们需要设置 $post 数据
		if ( isset( $post_object ) ) {
			$post = $post_object;
			setup_postdata( $post );
		}

		/**
		 * 按照顺序加载自定义位置或主题中的模版文件
		 */

		// 获取主题自定义模版目录名称
		$default_path       = rtrim( $default_path, '/' );
		$default_path_array = explode( '/', $default_path );
		$folder_name        = $default_path_array[ count( $default_path_array ) - 1 ];

		// 查找主题的优先顺序为：主题中的指定模版 > 插件中的指定模版 > 主题中的默认模版 > 插件中的默认模版
		if ( $name ) {

			// 优先查找主题中指定模板
			$located = locate_template( [ "{$folder_name}/{$slug}-{$name}.php", "{$slug}-{$name}.php" ] );

			// 如果主题中没有找到指定的模版，并且默认位置有这个模版，加载默认位置中的模板
			if ( '' === $located ) {
				if ( file_exists( $default_path . "/{$slug}-{$name}.php" ) ) {
					$located = $default_path . "/{$slug}-{$name}.php";
				} else {

					$located = locate_template( [ "{$folder_name}/{$slug}.php", "{$slug}.php" ] );

					if ( '' === $located ) {
						if ( file_exists( $default_path . "/{$slug}.php" ) ) {
							$located = $default_path . "/{$slug}.php";
						} else {
							return false;
						}
					}

				}
			}

		} else {

			// 优先查找主题中的指定默认模版
			$located = locate_template( [ "{$folder_name}/{$slug}.php", "{$slug}.php" ] );

			// 如果默认位置没有默认模版，加载主题中的默认模板
			if ( '' === $located && file_exists( $default_path . "/{$slug}.php" ) ) {
				$located = $default_path . "/{$slug}.php";
			}

		}

		/**
		 * 输出或返回渲染后的 HTML
		 */
		$return = false;
		if ( false === $echo ) {
			ob_start();
			if ( $located ) {
				require( $located );
			}
			$return = ob_get_clean();
		} else {
			require( $located );
		}

		/**
		 * 如果需要，重设变量
		 */
		if ( isset( $query ) ) {
			wp_reset_query();
		}

		if ( isset( $post_object ) ) {
			wp_reset_postdata();
		}

		if ( false === $echo ) {
			return $return;
		}

	}
}