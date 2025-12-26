// required for all view models:
/// <reference path='../../../../nutshell/pnut/core/ViewModelBase.ts' />
/// <reference path='../../../../nutshell/typings/knockout/index.d.ts' />

namespace Peanut {

    export class NationalRegistrationViewModel extends Peanut.ViewModelBase {
        // observables

        init(successFunction?: () => void) {
            let me = this;
            Peanut.logger.write('NationalRegistration Init');
            let fd = this.getPageVarialble('formdata');
            me.bindDefaultSection();
            successFunction();
        }
    }
}
