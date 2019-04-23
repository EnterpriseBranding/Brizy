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
			$projectPost        = Brizy_Editor_Project::getPost();
			$defaultProjectData = json_decode( file_get_contents( BRIZY_PLUGIN_PATH .
			                                                      DIRECTORY_SEPARATOR . "public" .
			                                                      DIRECTORY_SEPARATOR . "editor-build" .
			                                                      DIRECTORY_SEPARATOR . "defaults.json" ) );

			if ( ! $projectPost ) {
				return;
			}

			$storage = Brizy_Editor_Storage_Project::instance( $projectPost->ID );

			if ( $globals = $storage->get( 'globals' ) ) {
				update_post_meta( $projectPost->ID, 'brizy-bk-' . get_class( $this ) . '-' . $this->getVersion(), $storage->get_storage() );

				$beforeMergeGlobals        = json_decode( base64_decode( $globals ) );
				$mergedGlobals = self::mergeJson( $defaultProjectData, $beforeMergeGlobals );

				$storage->set( 'data', base64_encode( json_encode( $mergedGlobals ) ) );
				$storage->delete( 'globals' );

			}

		} catch ( Exception $e ) {
			return;
		}
	}

	static private function mergeJson( $json1, $json2 ) {
		if ( is_object( $json1 ) ) {
			$vals1 = get_object_vars( $json1 );
			$vals2 = get_object_vars( $json2 );

			foreach ( $vals2 as $key => $val ) {
				if ( isset( $vals1[ $key ] ) ) {
					$vals1[ $key ] = self::mergeJson( $vals1[ $key ], $vals2[ $key ] );
				} else {
					$vals1[ $key ] = $val;
				}
			}

			return (object)$vals1;
		}

		if ( is_array( $json1 ) ) {

			foreach ( $json2 as $key => $val ) {
				if ( isset( $json1[ $key ] ) ) {
					$json1[ $key ] = self::mergeJson( $json1[ $key ], $json2[ $key ] );
				} else {
					$json1[ $key ] = $val;
				}
			}

			return (object)$json1;
		}
	}




	public function getPriority() {
		return 10;
	}
}