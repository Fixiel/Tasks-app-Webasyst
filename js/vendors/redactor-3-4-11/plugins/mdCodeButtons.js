(function ($R) {
  $R.add("plugin", "mdCodeButtons", {
    init: function (app) {
      this.app = app;

      // define toolbar service
      this.toolbar = app.toolbar;
    },
    start: function () {
      // set up buttons
      var buttonCode = {
        title: "## code ##",
        icon: true,
        api: "plugin.mdCodeButtons.toggleCode",
      };

      var buttonQuote = {
        title: "## quote ##",
        icon: true,
        api: "module.block.format",
        args: {
          tag: "blockquote",
        },
      };

      // add buttons to the toolbar
      this.toolbar.addButton("html", buttonCode);
      this.toolbar.addButton("quote", buttonQuote);
    },
    toggleCode: function () {
      switch (this.app.selection.getElement().nodeName) {
        case "PRE": // clear code block
          this.app.block.format("pre");
          break;
        case "CODE": // clear code string
          this.app.inline.clearFormat();
          break;
        default:
          // string or block selected
          if (this.app.selection.isAll(this.app.selection.getElement())) {
            this.app.block.format("pre");
          } else {
            this.app.inline.format("code");
          }
      }
    },
  });
})(Redactor);
