<?php

class Brizy_Admin_Migrations_DefaultProjectDataMigration implements Brizy_Admin_Migrations_MigrationInterface {

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

			if ( ! $storage->get( 'data' ) ) {
				$storage->set( 'data', base64_encode( file_get_contents( BRIZY_PLUGIN_PATH .
				                                                         DIRECTORY_SEPARATOR . "public" .
				                                                         DIRECTORY_SEPARATOR . "editor-build" .
				                                                         DIRECTORY_SEPARATOR . "defaults.json" ) ) );
			}

		} catch ( Exception $e ) {
			return;
		}
	}

	public function getPriority() {
		return - 10;
	}

}