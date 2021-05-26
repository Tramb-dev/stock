// JavaScript Document
jQuery(function(){
	$('tr.produit').on('dblclick', function(){
		modifRef($(this).attr('id'));
	});
	
	$('.categorie').on('dblclick', function(){
		modifCat($(this).attr('id'));
	});
	
	$('.ajout').on('click', function(){
		ajout($(this).parent().attr('id'));
	});
	
	$('.retrait').on('click', function(){
		retrait($(this).parent().attr('id'));
	});

	$('.message').delay(10000).animate({color:'#000'}, 2000).fadeOut(1500);
	
	$table = $('#etat_ref');
	$table.tablesorter({
		cssChildRow : 'tablesorter-childRow',
		widgets: ['zebra', 'filter'],
		widgetOptions: {
			filter_childRows: true,
			filter_childByColumn: true,
			filter_childWithSibs: true,
			filter_ignoreCase: true,
		},
		headers: {
			// set "sorter : false" (no quotes) to disable the column
			0: {
				sorter: "text"
			},
			1: {
				sorter: false
			},
			2: {
				sorter: "date"
			},
			3: {
				sorter: "group-date-time"
			},
		},
		usNumberFormat: false,
		dateFormat: "ddmmyyyy",
	});
	$('.tablesorter-childRow td').hide();
	$('.tablesorter').on('click', '.toggle', function() {
		$(this).closest('tr').nextUntil('tr:not(.tablesorter-childRow)').find('td').toggle();
		return false;
	});
});

// Modifie une référence
function modifRef(selected){
	$.colorbox({
		href:'src/dialog.php?modif=true&id=' + selected,
		height: '90%',
		onComplete:function(){
			// Sécurise le bouton supprimer
			$('input[name="delete"]').on('click', function(){
				$('#formRef').submit(function(event){
					if(!confirm("Etes-vous sûr de vouloir supprimer cette référence ?"))
						event.preventDefault();
				});
			});
			$('input[name="retrait"]').on('click', function(){
				$('#formRef').submit(function(event){
					if(!confirm("Etes-vous sûr de vouloir supprimer cette référence ?"))
						event.preventDefault();
				});
			});
		}
	});
}

// Modifie une catégorie dans la gestion des catégories
function modifCat(selected){
	$.colorbox({
		href:'src/dialog.php?cat=true&id=' + selected,
		height: '90%',
		onComplete:function(){
			// Sécurise le bouton supprimer
			$('input[name="delete"]').on('click', function(){
				$('#formCat').submit(function(event){
					if(!confirm("Etes-vous sûr de vouloir supprimer cette catégorie ?"))
						event.preventDefault();
				});
			});
			$('input[name="retrait"]').on('click', function(){
				$('#formRef').submit(function(event){
					if(!confirm("Etes-vous sûr de vouloir supprimer cette catégorie ?"))
						event.preventDefault();
				});
			});
		}
	});
}

function ajout(selected){
	$.colorbox({
		href:'src/dialog.php?ajout=true&id=' + selected,
		height: '90%',
	});
}

function retrait(selected){
	$.colorbox({
		href:'src/dialog.php?retrait=true&id=' + selected,
		height: '90%',
	});
}