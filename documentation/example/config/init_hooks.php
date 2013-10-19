<?
/**
 *
 * Initialization Hooks
 *
 * List of functions that will happen when the framework loads
 *
 */
 
// init the bottrap
Registry::get("_framework")->load()->helper( "bot_honey_trap" );
bot_honey_trap_init();