<?php
/**
 * Command to generate indexables for all posts and terms.
 *
 * @package Yoast\YoastSEO\Commands
 */

namespace Yoast\WP\Free\Commands;

use wpdb;
use Yoast\WP\Free\Watchers\Indexable_Author_Watcher;
use Yoast\WP\Free\Watchers\Indexable_Post_Watcher;
use Yoast\WP\Free\Watchers\Indexable_Term_Watcher;
use Yoast\WP\Free\WordPress\WP_CLI_Command;

/**
 * Formats the term meta to indexable format.
 */
class Generate_Indexables_Command implements WP_CLI_Command {

	/**
	 * @var Indexable_Post_Watcher
	 */
	private $post_watcher;

	/**
	 * @var Indexable_Term_Watcher
	 */
	private $term_watcher;

	/**
	 * @var Indexable_Author_Watcher
	 */
	private $author_watcher;

	/**
	 * @var wpdb
	 */
	private $wpdb;

	public function __construct(
		Indexable_Post_Watcher $post_watcher,
		Indexable_Term_Watcher $term_watcher,
		Indexable_Author_Watcher $author_watcher,
		wpdb $wpdb
	) {
		$this->post_watcher = $post_watcher;
		$this->term_watcher = $term_watcher;
		$this->author_watcher = $author_watcher;
		$this->wpdb = $wpdb;
	}

	/**
	 * Returns the name of this command.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'yoast indexables generate';
	}

	/**
	 * Returns the configuration of this command.
	 *
	 * @return array
	 */
	public function get_config() {
		return [ 'shortdesc' => __( 'Generates indexables for all posts and terms.', 'wordpress-seo' ) ];
	}

	/**
	 * Executes this command.
	 *
	 * @return void
	 */
	public function execute() {
		$this->generate_for_posts();
		$this->generate_for_terms();
	}

	private function generate_for_posts() {
		$page     = 0;
		$total    = $this->wpdb->get_var( "SELECT COUNT(ID) FROM {$this->wpdb->posts};" );
		$progress = \WP_CLI\Utils\make_progress_bar( __( 'Indexing posts', 'wordpress-seo' ), $total );

		while ( true ) {
			$post_ids = $this->wpdb->get_col( $this->wpdb->prepare( "SELECT ID FROM {$this->wpdb->posts} ORDER BY ID ASC LIMIT %d, 25;", $page * 25 ) );

			if ( empty( $post_ids ) ) {
				break;
			}

			foreach( $post_ids as $post_id ) {
				$this->post_watcher->build_indexable( $post_id );
				$progress->tick();
			}

			$page += 1;
		}

		$progress->finish();
	}

	private function generate_for_terms() {
		$page     = 0;
		$total    = $this->wpdb->get_var( "SELECT COUNT(ID) FROM {$this->wpdb->terms};" );
		$progress = \WP_CLI\Utils\make_progress_bar( __( 'Indexing terms', 'wordpress-seo' ), $total );

		while ( true ) {
			$term_ids = $this->wpdb->get_col( $this->wpdb->prepare( "SELECT ID FROM {$this->wpdb->terms} ORDER BY ID ASC LIMIT %d, 25;", $page * 25 ) );

			if ( empty( $term_ids ) ) {
				break;
			}

			foreach( $term_ids as $term_id ) {
				$this->term_watcher->build_indexable( $term_id );
				$progress->tick();
			}

			$page += 1;
		}

		$progress->finish();
	}
}
