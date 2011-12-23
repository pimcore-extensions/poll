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
pimcore.registerNS("pimcore.plugin.poll.poll");

pimcore.plugin.poll.poll = Class.create(pimcore.element.abstract, {

    initialize: function(id) {
        this.id = intval(id);
        this.tabId = "poll_" + this.id;

        this.edit = new pimcore.plugin.poll.edit(this);
        this.report = new pimcore.plugin.poll.report(this);

        this.getData(this.addTab);
    },

    getData: function (callback) {
        Ext.Ajax.request({
            url: "/plugin/Poll/admin/get/",
            params: {
                id: this.id
            },
            success: function(response) {
                this.getDataComplete(response, callback.bind(this));
            }.bind(this)
        });
    },

    getDataComplete: function (response, callback) {
        try {
            this.data = Ext.decode(response.responseText);
            callback();
            this.startChangeDetector();
        } catch (e) {
            console.log(e.message);
            pimcore.helpers.showNotification(
                t("error"),
                t("error_opening_poll"),
                "error"
            );
        }
    },

    addTab: function () {
        this.tabPanel = Ext.getCmp("pimcore_panel_tabs");
        this.tab = new Ext.Panel({
            id: this.tabId,
            title: this.data.shortTitle,
            closable: true,
            layout: "border",
            items: [this.getLayoutToolbar(),this.getTabPanel()],
            poll: this,
            iconCls: "plugin_poll_icon"
        });

        this.tab.on("activate", function () {
            this.tab.doLayout();
            pimcore.layout.refresh();
        }.bind(this));

        this.tab.on("afterrender", function () {
            this.tabPanel.activate(this.tabId);
        }.bind(this));

        this.tab.on("destroy", function () {
            pimcore.globalmanager.remove(this.tabId);
        }.bind(this));

        this.tabPanel.add(this.tab);

        // recalculate the layout
        pimcore.layout.refresh();
    },

    getLayoutToolbar: function() {

        if (!this.toolbar) {
            var buttons = [];

            this.toolbarButtons = {};

            this.toolbarButtons.save = new Ext.SplitButton({
                text: t('save'),
                iconCls: "pimcore_icon_save_medium",
                scale: "medium",
                handler: this.save.bind(this),
                menu:[{
                    text: t('save_close'),
                    iconCls: "pimcore_icon_save",
                    handler: this.saveClose.bind(this)
                }]
            });

            this.toolbarButtons.publish = new Ext.SplitButton({
                text: t('save_and_publish'),
                iconCls: "pimcore_icon_publish_medium",
                scale: "medium",
                handler: this.publish.bind(this),
                menu: [{
                    text: t('save_pubish_close'),
                    iconCls: "pimcore_icon_save",
                    handler: this.publishClose.bind(this)
                }]
            });

            this.toolbarButtons.unpublish = new Ext.Button({
                text: t('unpublish'),
                iconCls: "pimcore_icon_unpublish_medium",
                scale: "medium",
                handler: this.unpublish.bind(this)
            });

            buttons.push(this.toolbarButtons.save);
            buttons.push(this.toolbarButtons.publish);
            buttons.push(this.toolbarButtons.unpublish);

            buttons.push("-");
            buttons.push({
                text: this.data.id,
                xtype: 'tbtext'
            });

            this.toolbar = new Ext.Toolbar({
                id: "poll_toolbar_" + this.id,
                region: "north",
                border: false,
                cls: "document_toolbar",
                items: buttons
            });

            this.toolbar.on("afterrender", this.updateToolbarButtons.bind(
                this, this.data.answers.length
            ));
        }

        return this.toolbar;
    },

    getTabPanel: function() {
        var items = [];

        items.push(this.edit.getLayout(this.data.layout));

        if(this.data.responses > 0) {
            items.push(this.report.getLayout());
        }

        var tabbar = new Ext.TabPanel({
            tabPosition: "top",
            region: 'center',
            deferredRender: true,
            enableTabScroll: false,
            border: false,
            items: items,
            activeTab: 0
        });

        return tabbar;
    },

    getSaveData: function() {
        var data = {};
        Ext.apply(data, this.data);

        // form data
        try {
            Ext.apply(data, this.edit.getValues());
        } catch (e) {
            pimcore.helpers.showNotification(
                t("error"),
                t("error_saving_poll"),
                "error"
            );
        }

        return data;
    },

    save: function() {
        var saveData = this.getSaveData();

        Ext.Ajax.request({
            url: '/plugin/Poll/admin/save/',
            method: "post",
            params: saveData,
            success: function (response) {
                try{
                    var rdata = Ext.decode(response.responseText);
                    if (rdata && rdata.success) {
                        pimcore.helpers.showNotification(
                            t("success"),
                            t("your_poll_has_been_saved"),
                            "success"
                        );
                    } else {
                        pimcore.helpers.showNotification(
                            t("error"),
                            t("error_saving_poll"),
                            "error",
                            rdata.message
                        );
                    }

                    this.getData(function(){
                        this.edit.poll = this;
                        this.edit.reload();
                        if (pimcore.globalmanager.exists("poll_list")) {
                            pimcore.globalmanager.get("poll_list").reload();
                        }
                    }.bind(this));

                } catch(e){
                    pimcore.helpers.showNotification(
                        t("error"),
                        t("error_saving_poll"),
                        "error"
                    );
                }
            }.bind(this)
        });

        this.resetChanges();

        return true;
    },

    updateToolbarButtons: function(answersCount) {
        if (!this.data.isActive) {
            this.toolbarButtons.unpublish.hide();
        }
        if (answersCount < 2) {
            this.toolbarButtons.save.show();
            this.toolbarButtons.publish.hide();
            this.toolbarButtons.unpublish.hide();
        } else {
            this.toolbarButtons.publish.show();
        }
    },

    saveClose: function() {
        this.save();
        var tabPanel = Ext.getCmp("pimcore_panel_tabs");
        tabPanel.remove(this.tab);
    },

    publish: function() {
        this.data.isActive = 1;

        if(this.save()) {
            // toogle buttons
            this.toolbarButtons.unpublish.show();
            this.toolbarButtons.save.hide();
        }
    },

    publishClose: function(){
        this.publish();
        var tabPanel = Ext.getCmp("pimcore_panel_tabs");
        tabPanel.remove(this.tab);
    },

    unpublish: function () {
        this.data.isActive = 0;

        if(this.save()) {
            // toogle buttons
            this.toolbarButtons.unpublish.hide();
            this.toolbarButtons.save.show();
        }
    },

    activate: function() {
        this.tabPanel.activate(this.tabId);
    }

});
