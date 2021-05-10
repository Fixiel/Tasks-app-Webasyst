const MarkdownIt = require("markdown-it");
const TurndownService = require("turndown").default;
const turndownService = new TurndownService();

// riles for the turndown
turndownService.addRule('pre', {
  filter: ['pre'],
  replacement: function (content) {
    return '```\n' + content + '\n```'
  }
})
turndownService.addRule('code', {
  filter: function (node, options) {
    return node.nodeName === 'CODE' && node.parentNode.nodeName !== 'PRE'
  },
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
    buttons: ['bold', 'italic', 'ol', 'ul', 'link'],
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
