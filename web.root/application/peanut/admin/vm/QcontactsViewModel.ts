/// <reference path='../../../../nutshell/pnut/core/ViewModelBase.ts' />
/// <reference path='../../../../nutshell/typings/knockout/index.d.ts' />

namespace Peanut {

    export class QcontactsViewModel extends Peanut.ViewModelBase {
        // observables
        testmessage = ko.observable('Contacts')

        init(successFunction?: () => void) {
            let me = this;
            Peanut.logger.write('VmName Init');

            me.bindDefaultSection();
            successFunction();
        }
    }
}