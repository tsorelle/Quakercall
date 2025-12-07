// required for all view models:
/// <reference path='../../../../nutshell/pnut/core/ViewModelBase.ts' />
/// <reference path='../../../../nutshell/typings/knockout/index.d.ts' />
/// <reference path='../../../../nutshell/pnut/js/htmlEditContainer.ts' />

namespace Peanut {

    export class EditorTestViewModel extends Peanut.ViewModelBase {
        // observables
        private htmlEditor : Peanut.htmlEditContainer;
        private i = 1;

        init(successFunction?: () => void) {
            let me = this;
            Peanut.logger.write('VmName Init');
            me.application.loadResources([
                '@pnut/htmlEditContainer'
            ], () => {
                me.htmlEditor =  new Peanut.htmlEditContainer(me);
                // set configuration
                //  me.htmlEditor.configureForContentEditing();
                // me.htmlEditor.configureForEmail() // default

                // set uptions
                me.htmlEditor.addOptions({height: '50ex'})
                // me.htmlEditor.addOptions({height: 300}) // height in pixels if numeric
                // me.htmlEditor.includeFontSizing();
                // me.htmlEditor.includeTableTools();
                me.htmlEditor.includeDesignTools();
                me.htmlEditor.initialize('test-editor', () => {
                    me.bindDefaultSection();
                    successFunction();
                })
            });
        }

/*
        // Late initialization example
        init(successFunction?: () => void) {
            let me = this;
            Peanut.logger.write('VmName Init');
            me.application.loadResources([
                '@pnut/htmlEditor'
            ], () => {
                // configure editor
                me.htmlEditor =  new Peanut.htmlEditor(me);
                me.htmlEditor.addOptions({height: '40ex'})
                me.htmlEditor.includeDesignTools()
                // passing null signals, don't initialize the editor you
                me.htmlEditor.initialize(null, () => {
                me.bindDefaultSection();
                successFunction();
                })
            });
        }

        showEditorDialog() {
            let me = this;
            // initialize the editor
            if (!me.htmlEditor.editorInitialized) {
                me.htmlEditor.initEditor('test-editor',() => {
                    me.htmlEditor.confirmEditorInit()
                    me.showModal('editor-dialog');
                });
            }
            else {
                me.showModal('editor-dialog');
            }
        }
*/
        getHtmlContent = () => {
            let content = this.htmlEditor.getContent();
            alert('Got content! "' + content.slice(0,20) + '..."' )
        }

        setHtmlContent = () => {
            this.htmlEditor.setContent('<h1>Hello World '+this.i+'</h1>');
            this.i++;
        }


    }
}
