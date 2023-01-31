define([
    'Magento_Ui/js/grid/columns/column',
    'jquery',
    'Magento_Ui/js/modal/modal'
], function (Column, $, modal) {
    'use strict';
    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html',
        },

  /*      getTitle: function (row) {
            return row[this.index + '_title'];
        },

        getLabel: function (row) {
            return row[this.index + '_html'];
        },*/

        getProductId: function (row) {
            return row[ 'data-id' ];
        },

        getRedunfData: function (row) {
            return row['data-refund'];
        },
        startView: function (row) {
            console.log(this.getRedunfData(row));
            if (false) {
                var previewPopup = $('<div/>',{id : 'fruugopopup'+this.getProductId(row) });
                var data = $.parseJSON(this.getRedunfData(row));
                var result = '<table class="data-grid" style="margin-bottom:25px"><tr><th style="padding:15px">Sl. No.</th><th style="padding:15px">SKU</th><th style="padding:15px">Errors</th></tr>';
                $.each(data, function(index, value){
                    var errors = "<table style='width: 100%;'>";
                    $.each(value.errors, function(i, v) {
                        errors += "<tr><th style='border: 0px; color: #0A0A0A; background: #ffffff;padding:10px;'>&#8608;</th><td style='border: 0px;'> " + v + "</td></tr>";
                        // errors += "<tr><td style='border: 0px;'> " + v + "</td></tr>";
                    });
                    errors += "</table>";
                    var sku = "<a href='" + value.url + "' target='_blank'>" + value.sku + "</a>";
                    result += '<tr><td style="vertical-align : middle">' + (index + 1) + '</td><td style="vertical-align : middle">'  + sku + '</td><td>' + errors + '</td></tr>';
                });
                result += '</table>';
                var fruugopopup = previewPopup.modal({
                    title: this.getTitle(row),
                    innerScroll: true,
                    modalLeftMargin: 15,
                    buttons: [],
                    opened: function (row) {
                        fruugopopup.append(result);
                    },
                    closed: function (row) { }
                }).trigger('openModal');
            }
        },

        getFieldHandler: function (row) {
            return this.startView.bind(this, row);
        },

    });

});
