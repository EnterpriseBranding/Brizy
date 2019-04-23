<?php

class Brizy_Admin_Migrations_GlobalsToDataMigration implements Brizy_Admin_Migrations_MigrationInterface {

	/**
	 * Return the version
	 *
	 * @return mixed
	 */
	public function getVersion() {
		return '1.0.73';
	}

	/**
	 * @return mixed|void
	 * @throws Exception
	 */
	public function execute() {

		try {
			$projectPost = Brizy_Editor_Project::getPost();
			$storage     = Brizy_Editor_Storage_Project::instance( $projectPost->ID );

			if ( $globals = $storage->get( 'globals' ) ) {
				update_post_meta( $projectPost->ID, 'brizy-bk-' . get_class( $this ) . '-' . $this->getVersion(), $storage->get_storage() );
				$storage->set( 'data', $globals );
				$storage->delete( 'globals' );

			}

		} catch ( Exception $e ) {
			return;
		}
	}

	public function getPriority() {
		return 10;
	}
}