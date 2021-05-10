(function($R)
{
   $R.add('plugin', 'mdCodeButtons', {
        translations: {
            en: {
                "codeString": "Code String",
                "codeBlock": "Code Block",
                "clear": "Clear"
            },
            ru: {
                "codeString": "Код строка",
                "codeBlock": "Код блок",
                "clear": "Очистить"
            }
        },
        init: function(app)
        {
            this.app = app;

            // define toolbar service
            this.toolbar = app.toolbar;
        },
        start: function()
        {
            // set up the button
            var buttonCode = {
                title: '## codeString ##',
                api: 'module.inline.format',
                args: {
                    tag: 'code'
                }
            };

            var buttonCodePre = {
                title: '## codeBlock ##',
                api: 'module.block.format',
                args: {
                    tag: 'pre'
                }
            };

            var buttonQuote = {
                title: '## quote ##',
                api: 'module.block.format',
                args: {
                    tag: 'blockquote'
                }
            };

            var resetStyles = {
                title: '## clear ##',
                api: 'module.block.format',
                args: {
                    tag: 'p'
                }
            };

            // add the button to the toolbar
            this.toolbar.addButton('buttonCode', buttonCode);
            this.toolbar.addButton('buttonCodePre', buttonCodePre);
            this.toolbar.addButton('buttonQuote', buttonQuote);
            this.toolbar.addButton('resetStyles', resetStyles);
        },
    });
})(Redactor);