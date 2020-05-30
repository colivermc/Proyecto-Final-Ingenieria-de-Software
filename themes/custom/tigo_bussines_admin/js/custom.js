(function ($) {
    $(document).ready(function () {

        $('#table-tigo-business').DataTable({
            "language": {
                "sProcessing": "Procesando...",
                "sLengthMenu": "Mostrar _MENU_ registros",
                "sZeroRecords": "No se encontraron resultados",
                "sEmptyTable": "Ningún dato disponible en esta tabla",
                "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                "sInfoPostFix": "",
                "sSearch": "Buscar: ",
                "sUrl": "",
                "sInfoThousands": ",",
                "sLoadingRecords": "Cargando...",
                "oPaginate": {
                    "sFirst": "Primero",
                    "sLast": "Último",
                    "sNext": "Siguiente",
                    "sPrevious": "Anterior"
                },
                "oAria": {
                    "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                    "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                }
            }
        });
        $('#table-tigo-business-warehouse').DataTable({
            "pageLength": 100,
            "language": {
                "sProcessing": "Procesando...",
                "sLengthMenu": "Mostrar _MENU_ registros",
                "sZeroRecords": "No se encontraron resultados",
                "sEmptyTable": "Ningún dato disponible en esta tabla",
                "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                "sInfoPostFix": "",
                "sSearch": "Buscar: ",
                "sUrl": "",
                "sInfoThousands": ",",
                "sLoadingRecords": "Cargando...",
                "oPaginate": {
                    "sFirst": "Primero",
                    "sLast": "Último",
                    "sNext": "Siguiente",
                    "sPrevious": "Anterior"
                },
                "oAria": {
                    "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                    "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                }
            }
        });
    });
    

    var sidenav = $('.sidenav');
    var sidenav_content = $('.content-sidenav');
    var hamburger = $('.hamburger');
    var overlay = $('.overlay-mobile');

    hamburger.on('click', function () {
        $(this).toggleClass('active');
        sidenav_content.toggleClass('con-collapse');
        sidenav.toggleClass('sid-collapse');
        overlay.toggleClass('open');
    });



    
    var inputs_numeros = [
        '#edit-limit',
        '#edit-clasification',
        '#edit-apn-id',
        '#edit-telefono',
        '#edit-ancho-banda'
    ]
    inputs_numeros.forEach(function (e) {
        $(e).on('input', function () {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });

        $("#edit-ip-asignada").on('input', function () {
            this.value = this.value.replace(/[^0-9.]/g, '');
        });

    $(document).ready(function () {
        // var row = $('.tarea_row').attr("class");
        //
        // var clases = String(row);
        // var clase = clases.replace("tarea_row tarea_", "");
        // clase = clase.replace(" odd", "");
        // clase = clase.trim();
        //
        // console.log(clase);
        // $('.tarea_row').css('background-color', String(clase));
    });



})(jQuery);
