<? $this->form_manager->get_errors(); ?>

<?=HTML::form_open( "" )?>
	<label>Username</label><input name="username" type="text" value="<?=$this->form_manager->get_value("username")?>"><br>
	<label>Password</label><input name="password" type="password"><br>
	<select name="type">
		<option value="" <?=$this->form_manager->get_select ("type", "" )?>>Select One</option>
		<option value="m" <?=$this->form_manager->get_select( "type", "m" )?>>Male</option>
		<option value="f" <?=$this->form_manager->get_select( "type", "f" )?>>Female</option>
	</select><br>
	<p>Feature</p>
	<label>Hair<input name="features[]" type="checkbox" value="hair"></label>
	<label>Eyes<input name="features[]" type="checkbox" value="eyes"></label>
	<label>Skin<input name="features[]" type="checkbox" value="skin"></label>
	<label>Feet<input name="features[]" type="checkbox" value="feet"></label>
	<br>
	<input name="submit" type="submit" value="Submit">
<?=HTML::form_close()?>