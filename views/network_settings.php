<h1>Network Posts Multisite Settings</h1>
<form method="post">
	<fieldset>
		<input type="hidden" name="_wpnonce" value="<?= $data['nonce']; ?>"/>
		<table class="form-table">
			<thead>
			<tr>
				<th>Blog name</th>
				<th>Strip excerpt tags</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach($data['rows'] as $row):?>
				<tr>
					<td><?= $row['blogname']; ?></td>
					<td class="check-column">
						<input type="checkbox" name="denied_tags[]" value="<?= $row['id']; ?>" <?= $row['denied_tags']; ?> />
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<input type="submit" name="submit" id="submit" class="button button-primary" value="Save"/>
	</fieldset>
</form>
