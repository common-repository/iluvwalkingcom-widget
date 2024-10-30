<?php
/*
Plugin Name: I Luv Walking Widget
Plugin URI: http://iluvwalking.com/get-the-widget
Description: Do you track your steps on ILuvWalking.com?  Then you need this widget for your blog.  Quickly and easily display your statistics on your own website!
Author: Nick Ohrn of Plugin-Developer.com
Version: 1.0.1
Author URI: http://plugin-developer.com
*/

if( !class_exists( 'ILuvWalkingWidget' ) ) {

	class ILuvWalkingWidget {
		
		var $site;
		var $version = '1.0.1';

		/**
		 * Appropriately registers all the action and filters.
		 *
		 * @return ILuvWalkingWidget
		 */
		function ILuvWalkingWidget( ) {
			add_action( 'widgets_init', array( &$this, 'registerWidgets' ) );
			add_action( 'init', array( &$this, 'enqueueNecessaryFiles' ) );
			
			$this->site = 'http://iluvwalking.com/';
		}

		function enqueueNecessaryFiles( ) {
			wp_enqueue_style( 'ilvuwalking-widget', WP_PLUGIN_URL . '/' . basename( dirname( __FILE__ ) ) . '/resources/iluvwalking-widget.css', array(), $this->version );
		}

		/**
		 * Register the ILuvWalking.com statistics widget and control.
		 *
		 */
		function registerWidgets( ) {
			wp_register_sidebar_widget( 'iluvwalking-com-widget', __( 'ILuvWalking.com Stats' ), array( &$this, 'widgetOutput' ) );
			wp_register_widget_control( 'iluvwalking-com-widget', __( 'ILuvWalking.com Stats' ), array( &$this, 'widgetControlOutput' ) );
		}

		/**
		 * Handles the output for the widget for the I Luv Walking widget.
		 *
		 */
		function widgetOutput( $args ) {
			extract( $args, EXTR_SKIP );
			echo $before_widget;
			
			$options = get_option( 'ILuvWalking.com Widget' );
			$widgetData = $this->getWidgetOutputData( $options[ 'name' ] );
			if( false === $widgetData ) {
				return;
			}
			
			if( !empty( $options[ 'title' ] ) ) {
				echo $before_title . $options[ 'title' ] . $after_title;
			} else {
				echo $before_title . __( 'I\'ve Been Busy Walking' ) . $after_title;
			}
			
			echo '<ul id="walker-tracker-widget-stats">';
			echo '<li><strong>' . __( 'Total Steps' ) . '</strong>' . $widgetData->total . '</li>';
			echo '<li><strong>' . __( 'Average Steps/Day' ) . '</strong>' . sprintf( '%d', $widgetData->average ) . '</li>';
			echo '<li><strong>' . __( 'Busiest Month' ) . ' (' . date( 'Y' ) . ')</strong>' . $widgetData->month . '</li>';
			echo '<li><strong>' . __( 'Busiest Day' ) . ' (' . date( 'Y' ) . ')</strong>' . $widgetData->day . '</li>';
			echo '</ul>';
			echo '<img id="walker-tracker-widget-logo" src="' . WP_PLUGIN_URL . '/' . basename( dirname( __FILE__ ) ) . '/resources/iluvwalking-widget-logo.gif' . '" alt="Walking" />';
			echo '<p id="walker-tracker-widget-get"><a href="http://iluvwalking.com/get-the-widget/">Get this widget</a> or <a href="http://iluvwalking.com/add-your-steps">log your steps</a><br /><a href="http://iluvwalking.com">ILuvWalking.com</a></p>';
			echo $after_widget;
		}

		/**
		 * Handles the output for the control for the I Luv Walking widget.
		 *
		 */
		function widgetControlOutput( ) {
			$options = get_option( 'ILuvWalking.com Widget' );
			if( isset( $_POST[ "iluvwalking-com-submit" ] ) ) {
				$options[ 'title' ] = strip_tags( stripslashes( $_POST[ 'iluvwalking-com-title' ] ) );
				$options[ 'name' ] = strip_tags( stripslashes( $_POST[ 'iluvwalking-com-name' ] ) );
			}
			
			update_option( 'ILuvWalking.com Widget', $options );
			
			$title = attribute_escape( $options[ 'title' ] );
			$name = attribute_escape( $options[ 'name' ] );
			
			include ( 'views/widget-control.php' );
		}

		/**
		 * Returns an associative array of data for the widget.
		 *
		 * @return array An associative
		 */
		function getWidgetOutputData( $username ) {
			$site = add_query_arg( array( 'walking-tracker-statistics-username' => $username ), $this->site );
			if( ini_get( 'allow_url_fopen' ) ) {
				$data = json_decode( file_get_contents( $site ) );
			} elseif( function_exists( 'curl_init' ) ) {
				$session = curl_init( $site );
				
				curl_setopt( $session, CURLOPT_HEADER, false );
				curl_setopt( $session, CURLOPT_RETURNTRANSFER, true );
				curl_setopt( $session, CURLOPT_FOLLOWLOCATION, false );
				
				$data = json_decode( curl_exec( $session ) );
				
				curl_close( $session );
			}
			
			return !isset( $data->total ) ? false : $data;
		}
	}
}

if( class_exists( 'ILuvWalkingWidget' ) ) {
	$iLuvWalkingWidget = new ILuvWalkingWidget( );
}
