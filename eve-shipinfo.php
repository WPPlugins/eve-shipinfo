<?php
/**
 * Plugin Name: EVE ShipInfo
 * Plugin URI: http://aeonoftime.com/EVE_Online_Tools/EVE-ShipInfo-WordPress-Plugin
 * Description: Puts an EVE Online ships database and ship fittings manager in your WordPress website, along with high quality screenshots and specialized shortcodes.
 * Version: 2.6
 * Author: Sebastian Mordziol
 * Author URI: http://www.aeonoftime.com
 * License: GPLv3
 * Text Domain: eve-shipinfo
 * Domain Path: /languages 
 *
 * Copyright 2014 Sebastian Mordziol (email : eve@aeonoftime.com)
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License, version 3, as
 *  published by the Free Software Foundation.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

// make sure we're in a normal wordpress environment
defined('ABSPATH') or die("No script kiddies please!");

// require the plugin's class file
require_once dirname(__FILE__).'/classes/EVEShipInfo.php';

// and let the class do its thing
EVEShipInfo::getInstance();