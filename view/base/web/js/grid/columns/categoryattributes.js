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

        getProducturl: function (row) {
            return row[this.index + '_producturl'];
        },

        getTitle: function (row) {
            return row[this.index + '_title'];
        },

        getLabel: function (row) {
            return row[this.index + '_html'];
        },

        getProductId: function (row) {
            return row[this.index + '_productid'];
        },

        getProductvalidation: function (row) {
            return row[this.index + '_productvalidation'];
        },
        startView: function (row) {
            if (this.getProductvalidation(row)) {
                var url_link = this.getProducturl(row);
                var previewPopup = $('<div/>',{id : 'fruugopopup'+this.getProductId(row) });
                var data = this.getProductvalidation(row);
                data = data.split(',');
                var result = '<table class="data-grid" style="margin-bottom:25px">';
                $.each(data, function(index, value){
                    result += '<tr><td>' + value + '</td></tr>';
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
