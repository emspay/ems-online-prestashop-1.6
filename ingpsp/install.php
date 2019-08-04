<?php
class ingpspInstall {
	public function createTables() {
		$db = Db::getInstance();

		if ( !$db->Execute( '
		DROP TABLE IF EXISTS `'._DB_PREFIX_.'ingpsp`;
		CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ingpsp` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`id_cart` int(11) DEFAULT NULL,
			`id_order` int(11) DEFAULT NULL,
			`key` varchar(64) NOT NULL,
			`ginger_order_id` varchar(36) NOT NULL,
			`payment_method` text,
			`reference` varchar(32) DEFAULT NULL,
			PRIMARY KEY (`id`),
			KEY `id_order` (`id_cart`),
			KEY `ginger_order_id` (`ginger_order_id`)
		) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1' ) )
			return false;

		return true;
	}

	public function createOrderState() {
		if ( !Configuration::get( 'ING_PSP_PENDING' ) ) {
			$orderState = new OrderState();
			$orderState->name = array();

			foreach ( Language::getLanguages() as $language ) {
				if ( Tools::strtolower( $language['iso_code'] ) == 'nl' )
					$orderState->name[$language['id_lang']] = 'Wachten op betaling';
				else
					$orderState->name[$language['id_lang']] = 'Waiting for payment';
			}

			$orderState->send_email = false;
			$orderState->color = '#9f00a7';
			$orderState->hidden = false;
			$orderState->delivery = false;
			$orderState->logable = false;
			$orderState->invoice = false;
			$orderState->paid = false;

			if ( !$orderState->add() )
				return false;

			Configuration::updateValue( 'ING_PSP_PENDING', (int)$orderState->id );
		}

		if ( !Configuration::get( 'ING_PSP_ERROR' ) ) {
			$orderState = new OrderState();
			$orderState->name = array();

			foreach ( Language::getLanguages() as $language ) {
				if ( Tools::strtolower( $language['iso_code'] ) == 'nl' )
					$orderState->name[$language['id_lang']] = 'Betaling mislukt';
				else
					$orderState->name[$language['id_lang']] = 'Payment Failed';
			}

			$orderState->send_email = false;
			$orderState->color = '#FF0000';
			$orderState->hidden = false;
			$orderState->delivery = false;
			$orderState->logable = false;
			$orderState->invoice = false;
			$orderState->paid = false;

			if ( !$orderState->add() )
				return false;

			Configuration::updateValue( 'ING_PSP_ERROR', (int)$orderState->id );
		}

		return true;
	}
}
