{% extends "layout.tpl" %}
{% block content %}
<h2><img src="{{ DESIGNPATH }}_images_panel/logos/action.png" alt="" />Ustawienia kategorii Action</h2>

<div class="block">
<div id="list-products"></div>
</div>

<script type="text/javascript">



/*<![CDATA[*/

function enableMulti(dg, ids, status) {
	return xajax_doChangeCategoryStatus(ids, dg, 1);
};

function disableMulti(dg, ids, status) {
	return xajax_doChangeCategoryStatus(ids, dg, 0);
};

var theDatagrid;

$(document).ready(function() {

	var column_id = new GF_Datagrid_Column({
		id: 'idcategory',
		caption: '{% trans %}TXT_ID{% endtrans %}',
		appearance: {
			width: 90,
			visible: false
		},
		filter: {
			type: GF_Datagrid.FILTER_BETWEEN,
		}
	});

	var column_name = new GF_Datagrid_Column({
		id: 'name',
		caption: '{% trans %}TXT_NAME{% endtrans %}',
		filter: {
			type: GF_Datagrid.FILTER_INPUT,
		},
		appearance: {
			align: GF_Datagrid.ALIGN_LEFT
		},
	});

	var column_symbol = new GF_Datagrid_Column({
		id: 'actionsymbol',
		caption: 'Symbol w Action',
		appearance: {
			width: 180,
			align: GF_Datagrid.ALIGN_LEFT
		},
		filter: {
			type: GF_Datagrid.FILTER_INPUT,
		}
	});

	var column_actionupdate = new GF_Datagrid_Column({
		id: 'actionupdate',
		caption: 'Aktualizuj kategorię z Action',
		appearance: {
			width: 210,
			align: GF_Datagrid.ALIGN_CENTER
		},
		filter: {
			type: GF_Datagrid.FILTER_SELECT,
			options: [
				{{ datagrid_filter.actionupdate }}
			],
		}
	});

	var column_margin = new GF_Datagrid_Column({
		id: 'actionmargin',
		caption: 'Marża sklepu w %',
		editable: true,
		appearance: {
			width: 180,
		},
	});

	var action_enableMulti = new GF_Action({
		img: '{{ DESIGNPATH }}/_images_panel/icons/datagrid/on.png',
		caption: '{% trans %}TXT_ENABLE{% endtrans %}',
		action: enableMulti,
	});
	
	var action_disableMulti = new GF_Action({
		img: '{{ DESIGNPATH }}/_images_panel/icons/datagrid/off.png',
		caption: '{% trans %}TXT_DISABLE{% endtrans %}',
		action: disableMulti,
	});

	var options = {
		id: 'actioncategory',
		mechanics: {
			key: 'idcategory',
			rows_per_page: 100,
			default_sorting: 'actionsymbol'
		},
		event_handlers: {
			load: xajax_LoadAllCategory,
			update_row: function(sId, oRow) {
				xajax_doUpdateCategory(sId, oRow.actionmargin);
				theDatagrid.LoadData();
			},
		},
		columns: [
			column_id,
			column_symbol,
			column_actionupdate,
			column_name,
			column_margin
		],
		group_actions: [
			action_enableMulti,
			action_disableMulti,
		],
	};

	theDatagrid = new GF_Datagrid($('#list-products'), options);

});

/*]]>*/


</script>
{% endblock %}