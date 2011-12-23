/**
 * ModernWeb
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.modernweb.pl/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@modernweb.pl so we can send you a copy immediately.
 *
 * @category    Pimcore
 * @package     Plugin_Poll
 * @author      Rafał Gałka <rafal@modernweb.pl>
 * @copyright   Copyright (c) 2007-2011 ModernWeb (http://www.modernweb.pl)
 * @license     http://www.modernweb.pl/license/new-bsd     New BSD License
 */
pimcore.registerNS("pimcore.plugin.poll.edit");

pimcore.plugin.poll.edit = Class.create({

    initialize: function(poll) {
        this.poll = poll;
    },

    getFormPanel: function(){

        if(this.formPanel) {
            return this.formPanel;
        }

        this.formPanel = new Ext.FormPanel({
            bodyStyle:'background-color: #fff;',
            padding: 10,
            border: false,
            region: "north",
            layout: "pimcoreform",
            autoHeight: true,
            items: [{
                xtype: "textfield",
                name: "title",
                value: this.poll.data.title,
                fieldLabel: t("question"),
                width: 300,
                labelStyle: "width:125px"
            },{
                xtype: 'compositefield',
                fieldLabel: t("publish_from"),
                combineErrors: false,
                labelStyle: "width:125px",
                items: [{
                    xtype: "datefield",
                    name: "startDate",
                    id: 'startDate' + this.poll.data.id,
                    vtype: 'daterange',
                    endDateField: 'endDate' + this.poll.data.id,
                    value: this.getDateTime(this.poll.data.startDate).date,
                    hideLabel: true,
                    width: 205
                },{
                    xtype: "timefield",
                    name: "startTime",
                    value: this.getDateTime(this.poll.data.startDate).time,
                    hideLabel: true,
                    increment: 30,
                    format: 'H:i',
                    width: 90
                }]
            },{
                xtype: 'compositefield',
                fieldLabel: t("publish_to"),
                combineErrors: false,
                labelStyle: "width:125px",
                items: [{
                    xtype: "datefield",
                    name: "endDate",
                    id: 'endDate' + this.poll.data.id,
                    vtype: 'daterange',
                    startDateField: 'startDate' + this.poll.data.id,
                    value: this.getDateTime(this.poll.data.endDate).date,
                    hideLabel: true,
                    width: 205
                },{
                    xtype: "timefield",
                    name: "endTime",
                    value: this.getDateTime(this.poll.data.endDate).time,
                    hideLabel: true,
                    increment: 30,
                    format: 'H:i',
                    width: 90
                }]
            },{
                xtype: 'checkbox',
                name: "multiple",
                fieldLabel: t("multiple_answers"),
                labelStyle: "width:125px",
                checked: this.poll.data.multiple
            }],
            listeners: {
                afterrender: function () {
                    pimcore.layout.refresh();
                }
            }
        });

        return this.formPanel;
    },

    getLayout: function () {
        if (this.layout) {
            return this.layout;
        }

        this.store = new Ext.data.Store({
            reader: new Ext.data.JsonReader({
                root: 'answers'
            }, [
                {name: 'id'},
                {name: 'title', allowBlank: false},
                {name: 'responses', allowBlank: false}
            ]),
            data: this.poll.data
        });
        this.store.on('remove', function(store){
            this.poll.updateToolbarButtons(store.getCount());
        }, this);

        var typesColumns = [
            {
                id: 'poll_grid_column_title',
                header: t("title"),
                width: 400,
                sortable: false,
                menuDisabled: true,
                dataIndex: 'title',
                editor: new Ext.form.TextField({})
            },{
                header: t("responses"),
                width: 80,
                sortable: false,
                menuDisabled: true,
                dataIndex: 'responses',
                align: 'right',
                editor: new Ext.form.TextField({readOnly:true})
            },{
                xtype: 'actioncolumn',
                width: 30,
                menuDisabled: true,
                items: [{
                    tooltip: t('delete'),
                    icon: "/pimcore/static/img/icon/cross.png",
                    handler: function (grid, rowIndex) {
                        grid.getStore().removeAt(rowIndex);
                    }.bind(this)
                }]
            }
        ];

        this.editor = new Ext.ux.grid.RowEditor();
        this.editor.on('afteredit', function(editor, changes, record, index){
            this.poll.updateToolbarButtons(this.store.getCount());
        }, this);

        this.answersGrid = new Ext.grid.GridPanel({
            title: t('answers'),
            store: this.store,
            frame: false,
            region: 'center',
            columnLines: true,
            stripeRows: true,
            border: false,
            plugins: [
                this.editor,
                new Ext.ux.dd.GridDragDropRowOrder({})
            ],
            columns : typesColumns,
            autoExpandColumn: 'poll_grid_column_title',
            sm: new Ext.grid.RowSelectionModel({singleSelect:true}),
            tbar: [{
                iconCls: "pimcore_icon_add",
                handler: this.onAnswerAdd.bind(this),
                text: t('add')
            }, '-',
            {
                text: t('delete'),
                handler: this.onAnswerDelete.bind(this),
                iconCls: "pimcore_icon_delete"
            }]
        });

        this.layout = new Ext.Panel({
            title: t('edit'),
            border: false,
            layout: "border",
            iconCls: "pimcore_icon_tab_edit",
            items: [this.getFormPanel(), this.answersGrid]
        });

        return this.layout;
    },

    onAnswerAdd: function() {
        var u = new this.answersGrid.store.recordType({
            title: "",
            responses: 0
        });
        this.editor.stopEditing();

        var index = this.answersGrid.store.getCount();
        this.answersGrid.store.insert(index, u);
        this.editor.startEditing(index);
    },

    onAnswerDelete: function() {
        var rec = this.answersGrid.getSelectionModel().getSelected();
        if (!rec) {
            return false;
        }
        this.answersGrid.store.remove(rec);
        return true;
    },

    getValues: function () {
        var data = this.getFormPanel().getForm().getFieldValues();

        data.multiple = (data.multiple) ? 1 : 0;
        data.startDate = this.formatDateTime(data.startDate, data.startTime);
        data.endDate = this.formatDateTime(data.endDate, data.endTime);

        var answers = [];
        this.store.each(function(rec, index){
            rec.data.index = index;
            answers.push(rec.data);
        });
        data.answers = Ext.encode(answers);

        return data;
    },

    reload: function() {
        this.store.loadData(this.poll.data);
    },

    getDateTime: function(value) {
        if(value) {
            var tmpDate = new Date(intval(value) * 1000);
            return {
                date: tmpDate,
                time: tmpDate.format("H:i")
            };
        }
        return {date: null, time: null};
    },

    formatDateTime: function(date, time) {
        if(!date) {
            return '';
        }

        var dateString = date.format("Y-m-d");

        if (time) {
            dateString += " " + time;
        } else {
            dateString += " 00:00";
        }
        return dateString;
    }

});
