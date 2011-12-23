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
pimcore.registerNS("pimcore.plugin.poll.list");

pimcore.plugin.poll.list = Class.create({

    initialize: function(plugin) {
        this.plugin = plugin;

        this.panel = this.getTabPanel();

        this.panel.on("destroy", function () {
            pimcore.globalmanager.remove("poll_list");
        }.bind(this));

        Ext.getCmp("pimcore_panel_tabs").add(this.panel);

        this.activate();
        pimcore.layout.refresh();
    },

    getTabPanel: function() {
        if (!this.panel) {
            this.panel = new Ext.Panel({
                id: "pimcore_plugin_poll_list",
                title: t("manage_polls"),
                iconCls: "plugin_poll_icon",
                border: false,
                layout: "fit",
                closable: true,
                items: [this.getGridPanel()]
            });
        }

        return this.panel;
    },

    getGridPanel: function() {
        var proxy = new Ext.data.HttpProxy({
            url: '/plugin/Poll/admin/list'
        });
        var reader = new Ext.data.JsonReader({
            totalProperty: 'total',
            successProperty: 'success',
            root: 'data'
        }, [
            {name: 'id'},
            {name: 'title', allowBlank: false},
            {name: 'creationDate', allowBlank: false},
            {name: 'startDate', allowBlank: true},
            {name: 'endDate', allowBlank: true},
            {name: 'isActive', type: 'bool', allowBlank: false},
            {name: 'multiple', type: 'bool', allowBlank: false},
            {name: 'current', type: 'bool', allowBlank: false},
            {name: 'viewsCount', type: 'int', allowBlank: false},
            {name: 'responses', type: 'int', allowBlank: false},
            {name: 'answers', allowBlank: true}
        ]);
        var writer = new Ext.data.JsonWriter();

        var itemsPerPage = 20;

        this.store = new Ext.data.Store({
            id: 'plugin_poll_list_store',
            restful: false,
            proxy: proxy,
            reader: reader,
            writer: writer,
            baseParams: {
                limit: itemsPerPage,
                filter: ""
            },
            listeners: {
                write: function(store, action, result, response) {
                    if(response.raw.refresh) {
                        this.pagingtoolbar.doRefresh();
                    }
                    if(response.raw.message) {
                        pimcore.helpers.showNotification(
                            t("error"),
                            t(response.raw.message),
                            "error"
                        );
                    }
                }.bind(this)
            }
        });
        this.store.load();

        this.filterField = new Ext.form.TextField({
            xtype: "textfield",
            width: 200,
            style: "margin: 0 10px 0 0;",
            enableKeyEvents: true,
            listeners: {
                "keydown" : function (field, key) {
                    if (key.getKey() == key.ENTER) {
                        var input = field;
                        this.store.baseParams.filter = input.getValue();
                        this.store.load();
                    }
                }.bind(this)
            }
        });

        this.pagingtoolbar = new Ext.PagingToolbar({
            pageSize: itemsPerPage,
            store: this.store,
            displayInfo: true,
            displayMsg: '{0} - {1} / {2}',
            emptyMsg: t("no_objects_found")
        });

        // add per-page selection
        this.pagingtoolbar.add("-");

        this.pagingtoolbar.add(new Ext.Toolbar.TextItem({
            text: t("items_per_page")
        }));
        this.pagingtoolbar.add(new Ext.form.ComboBox({
            store: [
                [10, "10"],
                [20, "20"],
                [50, "50"]
            ],
            mode: "local",
            width: 50,
            value: 20,
            triggerAction: "all",
            listeners: {
                select: function (box, rec, index) {
                    this.pagingtoolbar.pageSize = intval(rec.data.field1);
                    this.pagingtoolbar.moveFirst();
                }.bind(this)
            }
        }));

        var expander = new Ext.ux.grid.RowExpander({
            tpl: '{title} ({responses})',
            hideable: false,
            renderer : function(v, p, record){
                p.cellAttr = 'rowspan="2"';
                if(!record.data.answers.length) {
                    return '';
                }
                return '<div class="x-grid3-row-expander">&#160;</div>';
            },
            getBodyContent : function(record, index){
                if(!record.data.answers.length) {
                    return '';
                }

                var content = '<ul class="answers">';
                Ext.each(record.data.answers, function(answer) {
                    content += '<li>' + this.tpl.apply(answer) + '</li>';
                }.bind(this));
                content += '</ul>';

                return content;
            }
        });

        var typesColumns = [
            expander,
            {header: t("question"), width: 200, id: 'poll_list_column_title', sortable: true, hideable: false, dataIndex: 'title'},
            {header: t("creationDate"), width: 100, sortable: true, dataIndex: 'creationDate'},
            {header: t("startDate"), width: 100, sortable: false, dataIndex: 'startDate'},
            {header: t("endDate"), width: 100, sortable: false, dataIndex: 'endDate'},
            new Ext.grid.CheckColumn({
                header: t("published"),
                width: 40,
                sortable: false,
                menuDisabled: true,
                dataIndex: "isActive",
                renderer: this.checkboxRenderer.bind(this, "isActive")
            }),
            new Ext.grid.CheckColumn({
                header: t("multiple"),
                width: 40,
                sortable: true,
                menuDisabled: true,
                dataIndex: "multiple",
                renderer: this.checkboxRenderer.bind(this, "multiple")
            }),
            {
                header: t("current"),
                width: 40,
                align: 'center',
                sortable: true,
                menuDisabled: true,
                dataIndex: "current",
                renderer: function(value, metaData, record, rowIndex, colIndex, store){
                    if(value) {
                        metaData.css = "plugin_poll_grid_current";
                        return '';
                    }
                    return '-';
                }.bind(this)
            },
            {header: t("views"), width: 50, align: 'right', sortable: true, menuDisabled: true, dataIndex: 'viewsCount'},
            {header: t("responses"), width: 60, align: 'right', sortable: true, menuDisabled: true, dataIndex: 'responses'},
            new Ext.grid.ActionColumn({
                width: 20,
                align: 'center',
                menuDisabled: true,
                resizable: false,
                hideable: false,
                items: [{
                    tooltip: t('open'),
                    icon: "/pimcore/static/img/icon/chart_bar_edit.png",
                    handler: function (grid, index) {
                        this.plugin.openPollTab(grid.getStore().getAt(index).data.id);
                    }.bind(this)
                }]
            }),
            new Ext.grid.ActionColumn({
                width: 20,
                align: 'center',
                menuDisabled: true,
                resizable: false,
                hideable: false,
                items: [{
                    tooltip: t('delete'),
                    icon: "/pimcore/static/img/icon/cross.png",
                    handler: function (grid, index) {
                        this.onDelete(grid.getStore(), grid.getStore().getAt(index))
                    }.bind(this)
                }]
            })
        ];

        this.grid = new Ext.grid.EditorGridPanel({
            frame: false,
            autoScroll: true,
            trackMouseOver: true,
            store: this.store,
            columnLines: true,
            stripeRows: true,
            columns: typesColumns,
            autoExpandColumn: 'poll_list_column_question',
            sm: new Ext.grid.RowSelectionModel({singleSelect:true}),
            plugins: expander,
            bbar: this.pagingtoolbar,
            tbar: [
                {
                    text: t('create_poll'),
                    handler: this.onAdd.bind(this),
                    iconCls: "plugin_poll_icon_add"
                },
                '-',
                {
                    text: t('delete'),
                    handler: function(){
                        this.onDelete(
                            this.grid.getStore(),
                            this.grid.getSelectionModel().getSelected()
                        );
                    }.bind(this),
                    iconCls: "pimcore_icon_delete"
                },
                '-',"->",{
                  text: t("filter") + "/" + t("search"),
                  xtype: "tbtext",
                  style: "margin: 0 10px 0 0;"
                },
                this.filterField
            ],
            viewConfig: {forceFit: true}
        });
        this.grid.on("rowcontextmenu", this.onRowContextmenu.bind(this));

        return this.grid;
    },

    onAdd: function() {
        this.plugin.addPoll();
    },

    onDelete: function(store, record) {
        Ext.MessageBox.confirm(
            t("delete"),
            t("poll_delete_message"),
            function (buttonValue) {
                if (buttonValue == "yes") {
                    store.remove(record);
                    store.load();
                }
            }.bind(this)
        );
        return true;
    },

    onRowContextmenu: function (grid, index, event) {
        var menu = new Ext.menu.Menu();

        menu.add(new Ext.menu.Item({
            text: t('open'),
            iconCls: "plugin_poll_icon_edit",
            handler: function() {
                this.plugin.openPollTab(grid.getStore().getAt(index).data.id);
            }.bind(this)
        }));
        menu.add(new Ext.menu.Item({
            text: t('delete'),
            iconCls: "pimcore_icon_delete",
            handler: this.onDelete.bind(this, grid.getStore(), grid.getStore().getAt(index))
        }));

        event.stopEvent();
        menu.showAt(event.getXY());
    },

    checkboxRenderer: function (field, value, metaData, record, rowIndex, colIndex, store) {
        if(field == 'isActive' && record.data.answers.length < 2) {
            return '';
        }
        metaData.css += ' x-grid3-check-col-td';
        return String.format('<div class="x-grid3-check-col{0}">&#160;</div>', value ? '-on' : '');
    },

    activate: function() {
        var tabPanel = Ext.getCmp("pimcore_panel_tabs");
        tabPanel.activate("pimcore_plugin_poll_list");
    },

    reload: function() {
        this.store.load();
    }

});
