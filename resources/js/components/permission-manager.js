function permissionManager(users = [], roles = [], branches = []) {
    return {
        selectedType: 'user',
        selectedId: '',
        selectedBranchId: '',
        branchAccessType: 'specific',
        selectedBranches: [],
        selectedPermissions: [],
        loading: false,
        error: null,
        users,
        roles,
        branches,

        init() {
            this.$watch('branchAccessType', value => {
                if (value === 'all') {
                    this.selectedBranches = [];
                    this.updateBranchAccess();
                }
            });

            this.$watch('selectedBranches', () => {
                this.updateBranchAccess();
            });
        },

        async selectAllInGroup(groupId) {
            if (!this.selectedId) {
                this.error = 'Please select a user or role first';
                return;
            }

            const groupPermissions = document.querySelectorAll(
                `input[data-group-id="${groupId}"][type="checkbox"]:not(:checked)`
            );

            const permissionIdsToAdd = Array.from(groupPermissions)
                .map(checkbox => parseInt(checkbox.value));

            this.loading = true;
            try {
                for (const permissionId of permissionIdsToAdd) {
                    await this.updatePermissionBulk(permissionId, true);
                }

                await this.loadPermissions();

                this.$dispatch('show-alert', {
                    type: 'success',
                    title: 'Success',
                    message: 'All permissions in group selected'
                });
            } catch (error) {
                console.error('Error selecting all permissions:', error);
                this.error = error.message;
            } finally {
                this.loading = false;
            }
        },

        async deselectAllInGroup(groupId) {
            if (!this.selectedId) {
                this.error = 'Please select a user or role first';
                return;
            }

            const groupPermissions = document.querySelectorAll(
                `input[data-group-id="${groupId}"][type="checkbox"]:checked`
            );

            const permissionIdsToRemove = Array.from(groupPermissions)
                .map(checkbox => parseInt(checkbox.value));

            this.loading = true;
            try {
                for (const permissionId of permissionIdsToRemove) {
                    await this.updatePermissionBulk(permissionId, false);
                }

                await this.loadPermissions();

                this.$dispatch('show-alert', {
                    type: 'success',
                    title: 'Success',
                    message: 'All permissions in group deselected'
                });
            } catch (error) {
                console.error('Error deselecting all permissions:', error);
                this.error = error.message;
            } finally {
                this.loading = false;
            }
        },

        async updatePermissionBulk(permissionId, granted) {
            const response = await fetch('/admin/permissoes/atualizar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    type: this.selectedType,
                    id: this.selectedId,
                    permissionId: permissionId,
                    branchId: this.selectedBranchId,
                    granted: granted,
                    branchAccessType: this.branchAccessType,
                    selectedBranches: this.selectedBranches
                })
            });

            if (!response.ok) {
                throw new Error(await response.text());
            }

            return response;
        },

        async loadPermissions() {
            if (!this.selectedId) {
                this.selectedPermissions = [];
                return;
            }

            this.loading = true;
            try {
                const response = await fetch(`/admin/permissoes/obter-permissoes/${this.selectedType}/${this.selectedId}/${this.selectedBranchId || 'all'}`, {
                    headers: {
                        'X-Alpine-Request': 'true',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                this.selectedPermissions = data.permissions || [];
                this.branchAccessType = data.branchAccessType || 'specific';
                this.selectedBranches = data.selectedBranches || [];

            } catch (error) {
                console.error('Error loading:', error);
                this.error = error.message;
            } finally {
                this.loading = false;
            }
        },

        async updateBranchAccess() {
            if (!this.selectedId) return;

            this.loading = true;
            try {
                const response = await fetch('/admin/permissoes/atualizar-filiais', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        type: this.selectedType,
                        id: this.selectedId,
                        branchAccessType: this.branchAccessType,
                        branches: this.branchAccessType === 'specific' ? this.selectedBranches : []
                    })
                });

                if (!response.ok) {
                    throw new Error(await response.text());
                }

                this.$dispatch('show-alert', {
                    type: 'success',
                    title: 'Success',
                    message: 'Branch access updated'
                });

            } catch (error) {
                console.error('Error:', error);
                this.error = error.message;
            } finally {
                this.loading = false;
            }
        },

        async updatePermission(event, permissionId) {
            if (!this.selectedId) {
                event.preventDefault();
                this.error = 'Please select a user or role first';
                return;
            }

            this.loading = true;
            try {
                const response = await fetch('/admin/permissoes/atualizar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        type: this.selectedType,
                        id: this.selectedId,
                        permissionId: permissionId,
                        branchId: this.selectedBranchId,
                        granted: event.target.checked,
                        branchAccessType: this.branchAccessType,
                        selectedBranches: this.selectedBranches
                    })
                });

                if (!response.ok) {
                    throw new Error(await response.text());
                }

                this.$dispatch('show-alert', {
                    type: 'success',
                    title: 'Success',
                    message: 'Permission updated'
                });

            } catch (error) {
                console.error('Error:', error);
                event.target.checked = !event.target.checked;
                this.error = error.message;
            } finally {
                this.loading = false;
            }
        },

        resetSelection() {
            this.selectedId = '';
            this.selectedBranchId = '';
            this.selectedPermissions = [];
            this.branchAccessType = 'specific';
            this.selectedBranches = [];
            this.error = null;
        }
    };
}

window.permissionManager = permissionManager;
