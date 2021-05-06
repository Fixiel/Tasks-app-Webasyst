class MdEditor {
    constructor(target, options = {}) {
        this.target = target;
        this.targetElement = document.querySelector(this.target);
        this.targetElement.style.display = 'none'

        const id = '_' + Math.random().toString(36).substr(2, 9);
        var newDiv = document.createElement("div");
        newDiv.id = id
        this.targetElement.parentNode.insertBefore(newDiv, this.targetElement);

        this.quill = new Quill(`#${id}`, {
            modules: {
                toolbar: [
                    [{ header: [1, 2, false] }],
                    ["bold", "italic", "underline"],
                    [{ list: "ordered" }, { list: "bullet" }],
                    ["image", "code-block"],
                ],
            },
            theme: "snow",
        });

        const md = window.markdownit();
        md.set({
            html: true,
        });

        const result = md.render(this.targetElement.value);

        this.quill.clipboard.dangerouslyPasteHTML(result + "\n");

        this.quill.on("text-change", (delta, source) => {
            const html = this.quill.container.firstChild.innerHTML;
            const markdown = toMarkdown(html);
            this.targetElement.value = markdown;
        });
    }
}
