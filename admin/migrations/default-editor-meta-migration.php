<?php

class Brizy_Admin_Migrations_DefaultEditorMetaMigration implements Brizy_Admin_Migrations_MigrationInterface {

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
			$project = Brizy_Editor_Project::get();
			if ( ! $project->getEditorMeta() ) {
				$project->setEditorMetaAsJson( file_get_contents( BRIZY_PLUGIN_PATH .
				                                                  DIRECTORY_SEPARATOR . "public" .
				                                                  DIRECTORY_SEPARATOR . "editor-build" .
				                                                  DIRECTORY_SEPARATOR . "defaults.json" ) )
				        ->save();
			}

		} catch ( Exception $e ) {
			return;
		}
	}

}