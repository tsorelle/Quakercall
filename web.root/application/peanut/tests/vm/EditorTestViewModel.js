var Peanut;
(function (Peanut) {
    class EditorTestViewModel extends Peanut.ViewModelBase {
        constructor() {
            super(...arguments);
            this.i = 1;
            this.getHtmlContent = () => {
                let content = this.htmlEditor.getContent();
                alert('Got content! "' + content.slice(0, 20) + '..."');
            };
            this.setHtmlContent = () => {
                this.htmlEditor.setContent('<h1>Hello World ' + this.i + '</h1>');
                this.i++;
            };
        }
        init(successFunction) {
            let me = this;
            Peanut.logger.write('VmName Init');
            me.application.loadResources([
                '@pnut/htmlEditContainer'
            ], () => {
                me.htmlEditor = new Peanut.htmlEditContainer(me);
                me.htmlEditor.addOptions({ height: '50ex' });
                me.htmlEditor.includeDesignTools();
                me.htmlEditor.initialize('test-editor', () => {
                    me.bindDefaultSection();
                    successFunction();
                });
            });
        }
    }
    Peanut.EditorTestViewModel = EditorTestViewModel;
})(Peanut || (Peanut = {}));
//# sourceMappingURL=EditorTestViewModel.js.map