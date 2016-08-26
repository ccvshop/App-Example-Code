<?php
	namespace AppConnector;
	/**
	 * Class Config
	 * Handles all configurations
	 *
	 * @package \
	 * @author  Nick Postma
	 * @date    2016-06-13
	 * @version 1.0    - First draft
	 */
	class Config {
		/**
		 * This is the base of the app URI.
		 * This Uri is used in the update schema example
		 * Note: Remove possible trailing slashes.
		 */
		const AppUri = 'https://ameijer-app.ccvdev.nl';

		/**
		 * This contains a secret key which is unique for this App.
		 * You can find this as a property of the App in the Developer App Center
		 * Example: 'dsadsakldjsakljdklsajdklsajdkljas'
		 * This key is used in the AppConnector.php
		 */
		const AppSecretKey = '63vyl94ceu48yfdbqxjwi5dp39ko6mo2';

		/**
		 * This is the URI of the handshake. Use this to validate calls from the App store.
		 * Example: https://demo.securearea.eu/Handshake.php
		 * This Uri is used in the AppConnector.php
		 */
		const AppHandshakeUri = 'https://ameijer-app.ccvdev.nl/Handshake.php';

		/**
		 * This is the URI of the Uninstall. Use this to validate calls from the App store.
		 * Example: https://demo.securearea.eu/UnInstall.php
		 * This Uri is used in the AppConnector.php
		 */
		const AppUninstallUri = 'https://ameijer-app.ccvdev.nl/UnInstall.php';

		/**
		 * Default setting for storing credentials.
		 * - JSON :: This stores credentials in Data\data.credential.txt
		 * - SQL :: This stores the credentials in a MySQL table. Use Data\data.credential.sql for the table setup
		 */
		const CredentialStorageType = 'JSON';

		/**
		 * If CredentialStorageType is SQL setup the databasehost for storage
		 * This setting is used in Sql\Connecion.php called by Data_Credential.php
		 */
		const DatabaseHost = 'localhost';

		/**
		 * If CredentialStorageType is SQL setup the databasename for storage
		 * This setting is used in Sql\Connecion.php called by Data_Credential.php
		 */
		const DatabaseName = 'biedmeer_system';

		/**
		 * If CredentialStorageType is SQL setup the databaseuser for storage
		 * This setting is used in Sql\Connecion.php called by Data_Credential.php
		 */
		const DatabaseUsername = 'biedmeer_system';

		/**
		 * If CredentialStorageType is SQL setup the databasepassword for storage
		 * This setting is used in Sql\Connecion.php called by Data_Credential.php
		 */
		const DatabasePassword = 'bied1960';

	}