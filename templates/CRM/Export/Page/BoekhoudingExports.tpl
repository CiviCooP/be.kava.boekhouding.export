<div class="crm-content-block crm-block">
	<a href="{ $nieuw_url }" class="button" style="float:left; margin-bottom: 10px;"><span>Nieuwe export</span></a><div style="clear:both;"></div>
	<div id="help">Hieronder vindt u een lijst met alle boekhoud-exports gesorteerd op datum.</div>
	<div id="boekhoudingexport-wrapper">
		<table id="boekhoudingexport-table">
			<thead>
				<tr>
					<th class="sorting-disabled">Periode</th>
					<th class="sorting-disabled">Aanvraag</th>
					<th class="sorting-disabled">Bestand</th>
					<th class="sorting-disabled">Acties</th>
				</tr>
			</thead>
		<tbody>
			{foreach from=$exports key=id item=export}
				<tr>
					<td>{ $export.periode_start } t/m { $export.periode_stop }</td>
					<td>{ $export.contact_id } op { $export.created_at }</td>
					<td><a href="{ $export.filenameurl }">{ $export.filename }</td>
					<td><a href="{ $export.verwijderen }">Verwijderen</a></td>
				</tr>
			{/foreach}
		</tbody>
		</table>    
	</div>
</div>