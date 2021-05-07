const MarkdownIt = require("markdown-it");
const TurndownService = require("turndown").default;
const turndownService = new TurndownService();
turndownService.addRule('code', {
  filter: ['pre'],
  replacement: function (content) {
    return '`' + content + '`'
  }
})

export default (target, options = {}) => {
  const md = MarkdownIt();
  md.set({
    html: true,
  });

  $R(target, {
    ...options,
    buttons: ['format', 'bold', 'italic', 'ol', 'ul', 'link'],
    formatting: ['p', 'blockquote', 'pre'],
    source: false,
    callbacks: {
      started: function () {
        // get started md and make it html
        this.source.setCode(md.render(this.source.getStartedContent()));
      },
      syncing: function (html) {
        return turndownService.turndown(html);
      },
    },
  });
};
