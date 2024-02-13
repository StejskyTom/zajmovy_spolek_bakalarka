// Call the dataTables jQuery plugin
$(document).ready(function() {
    $('#dataTable').DataTable({
        "language": {
            "lengthMenu": "Zobrazit _MENU_ záznamů na stránku",
            "zeroRecords": "Nenalezli jsme žádné záznamy",
            "info": "Zobrazení stránky _PAGE_ z _PAGES_",
            "infoEmpty": "Nejsou dostupné žádné záznamy",
            "infoFiltered": "(filtrováno z _MAX_ záznamů)",
            "search": "Vyhledat:",
            "paginate": {
                 "first": "První",
                 "last": "Poslední",
                 "next": "Další",
                 "previous": "Předchozí"
            },
            "loadingRecords": "Načítání..."
        }
    });
});
