var PeanutPermissions;
(function (PeanutPermissions) {
    class PermissionsViewModel extends Peanut.ViewModelBase {
        constructor() {
            super(...arguments);
            this.roles = [];
            this.permissionsList = ko.observableArray([]);
            this.permissionForm = {
                permissionName: ko.observable(''),
                assigned: ko.observableArray([]),
                available: ko.observableArray([]),
                changed: ko.observable(false)
            };
            this.waitLabelGetPermissions = 'Getting permissions';
            this.waitLabelUpdatePermissions = 'Updating permissions';
            this.getPermissions = (finalFunction) => {
                let me = this;
                let request = {};
                me.application.hideServiceMessages();
                me.application.showWaiter(me.waitLabelGetPermissions + '...');
                me.services.executeService('peanut.peanut-permissions::GetPermissions', request, function (serviceResponse) {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                        let response = serviceResponse.Value;
                        me.permissionsList(response.permissions);
                        me.roles = response.roles;
                        me.addTranslations(response.translations);
                        me.waitLabelGetPermissions = response.translations['permission-wait-get'];
                        me.waitLabelUpdatePermissions = response.translations['permission-wait-update'];
                    }
                    if (finalFunction) {
                        finalFunction();
                    }
                }).fail(function () {
                    let trace = me.services.getErrorInformation();
                }).always(() => {
                    me.application.hideWaiter();
                });
            };
            this.updatePermission = () => {
                let me = this;
                Peanut.ui.helper.hideModal("permission-modal");
                let request = {
                    permissionName: me.permissionForm.permissionName(),
                    roles: me.permissionForm.assigned()
                };
                me.application.hideServiceMessages();
                me.application.showWaiter(me.waitLabelUpdatePermissions);
                me.services.executeService('peanut.peanut-permissions::UpdatePermission', request, function (serviceResponse) {
                    me.application.hideWaiter();
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                        let response = serviceResponse.Value;
                        me.permissionsList(response);
                    }
                }).fail(function () {
                    let err = me.services.getErrorInformation();
                    me.application.hideWaiter();
                });
            };
            this.showPermissionUpdateForm = (selected) => {
                let me = this;
                me.permissionForm.permissionName(selected.permissionName);
                let available = Peanut.Helper.ExcludeValues(me.roles, selected.roles, 'Key');
                me.permissionForm.assigned(selected.roles);
                me.permissionForm.assigned.sort((left, right) => {
                    return left.Key.localeCompare(right.Key);
                });
                me.permissionForm.available(available);
                me.permissionForm.available.sort((left, right) => {
                    return left.Key.localeCompare(right.Key);
                });
                me.permissionForm.changed(false);
                Peanut.ui.helper.showModal('permission-modal');
            };
            this.onAddRole = (selected) => {
                let me = this;
                me.permissionForm.assigned.push(selected);
                me.permissionForm.available.remove(selected);
                me.permissionForm.assigned.sort((left, right) => {
                    return left.Key.localeCompare(right.Key);
                });
                me.permissionForm.changed(true);
            };
            this.onRemoveRole = (selected) => {
                let me = this;
                me.permissionForm.assigned.remove(selected);
                me.permissionForm.available.push(selected);
                me.permissionForm.available.sort((left, right) => {
                    return left.Key.localeCompare(right.Key);
                });
                me.permissionForm.changed(true);
            };
        }
        init(successFunction) {
            let me = this;
            Peanut.logger.write('VM Init');
            me.application.loadResources([
                '@lib:lodash'
            ], () => {
                me.getPermissions(() => {
                    me.bindDefaultSection();
                    successFunction();
                });
            });
        }
    }
    PeanutPermissions.PermissionsViewModel = PermissionsViewModel;
})(PeanutPermissions || (PeanutPermissions = {}));
//# sourceMappingURL=PermissionsViewModel.js.map