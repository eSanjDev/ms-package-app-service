import BaseTable from './BaseTable.js';

class ServiceTable extends BaseTable {
    constructor() {
        super('services', 'Service', {
            includesRelations: ``,
        });
    }

    getColumns() {
        let columns = [
            {data: ''},
            {data: 'name'},
            {data: 'client_id'},
            {data: 'status'},
        ];

        if (this.params.flagOnlyTrashed) {
            columns.push({data: 'deleted_at', title: 'Deleted At'});
        }

        columns.push({data: 'actions'});

        return columns;
    }

    getCustomFilters() {
        return {}
    }


    getColumnDefinitions() {
        const flagOnlyTrashed = this.params.flagOnlyTrashed;
        const resourceName = this.resourceName;
        return [
            ...super.getColumnDefinitions(),
            {
                targets: 1,
                data: 'name',
                title: 'Name',
                render(data, type, full) {
                    return `<strong>${full.name}</strong>`;
                }
            },
            {
                targets: 2,
                data: 'client_id',
                title: 'Client ID',
                render(data, type, full) {
                    return `<span>${full['client_id']}</span>`;
                }
            },
            {
                targets: 3,
                data: 'status',
                title: 'Status',
                render: function (data, type, full, meta) {
                    const status = full['status']
                    let output = ''
                    const statusBadgeObj = {
                        1: '<span class="badge bg-label-success me-1">Active</span>',
                        0: '<span class="badge bg-label-danger me-1">Disable</span>'
                    }

                    output = statusBadgeObj[status] || `<span class="badge bg-label-secondary me-1">${status}</span>`

                    return '<span class="text-nowrap">' + output + '</span>'
                }
            },
            {
                targets: -1,
                responsivePriority: 6,
                data: 'actions',
                title: 'Actions',
                searchable: false,
                orderable: false,
                render(data, type, full) {
                    return flagOnlyTrashed ? `
                        <div class="d-flex align-items-center">
                            <a href="javascript:;" class="text-body recovery-record"><i class="icon-base ti tabler-restore"></i></a>
                        </div>` :
                        `<div class="d-flex align-items-center">
                            <span class="text-nowrap">
                                <a href="${baseUrlAdmin}/${resourceName}/${full['id']}/edit" class="btn btn_action p-2">
                                    <i class="icon-base ti tabler-edit"></i>
                                </a>
                                <a href="#" class="btn btn_action delete-record p-2">
                                    <i class="icon-base ti tabler-delete"></i>
                                </a>
                            </span>
                        </div>`
                }
            }
        ];
    }

    transformResponse(response) {
        return response.data.map(entityItem => ({
            id: entityItem.id,
            name: entityItem.name,
            client_id: entityItem.client_id,
            status: entityItem.status,
            deleted_at: entityItem.deleted_at,
            actions: ''
        }))
    }
}

const serviceTable = new ServiceTable();

serviceTable.init();
