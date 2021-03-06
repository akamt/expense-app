<template>
    <el-main v-loading.fullscreen.lock="periodLoading">
        <div class="selector-wrapper">
            <el-select v-model="period" @change="getList" clearable placeholder="対象月を選択">
                <el-option
                        v-for="item in periodList"
                        :key="item.month"
                        :label="item.month"
                        :value="item.month">
                </el-option>
            </el-select>
        </div>

        <div class="button-wrapper">
            <el-button type="primary" :disabled="tableData.length === 0" @click="csvDownload">
                CSVを出力
            </el-button>
        </div>

        <el-table v-loading="listLoading" class="tb-edit" :data="tableData" highlight-current-row
                  @row-click="handleCurrentChange" height="calc(100% - 50px)" @selection-change="handleSelectionChange"
                  :default-sort="{prop: 'use_day', order: 'descending'}">
            <span slot="empty">
                対象月を選択
            </span>
            <el-table-column
                    type="selection"
                    width="55">
            </el-table-column>
            <el-table-column
                    label="日付"
                    prop="use_day"
                    sortable>
                <template slot-scope="scope">
                    <el-date-picker v-model="scope.row.use_day" value-format="yyyy-MM-dd" type="date"
                                    placeholder="対象日を選択" @change="handleEdit(scope.$index, scope.row)"
                                    style="width:100%;">
                    </el-date-picker>
                    <span>{{ scope.row.use_day }}</span>
                </template>
            </el-table-column>
            <el-table-column
                    prop="name"
                    label="経費名">
                <template slot-scope="scope">
                    <el-input v-model="scope.row.name" placeholder="経費名"
                              @change="handleEdit(scope.$index, scope.row)">
                    </el-input>
                    <span>{{ scope.row.name }}</span>
                </template>
            </el-table-column>
            <el-table-column
                    prop="payment"
                    label="支払先">
                <template slot-scope="scope">
                    <el-input v-model="scope.row.payment" placeholder="支払先"
                              @change="handleEdit(scope.$index, scope.row)">
                    </el-input>
                    <span>{{ scope.row.payment }}</span>
                </template>
            </el-table-column>
            <el-table-column
                    prop="value"
                    label="値段"
                    sortable>
                <template slot-scope="scope">
                    <el-input v-model.number="scope.row.value" placeholder="値段"
                              @change="handleEdit(scope.$index, scope.row)">
                    </el-input>
                    <span>{{ scope.row.value }}</span>
                </template>
            </el-table-column>
            <el-table-column
                    prop="category_name"
                    label="経費項目">
                <template slot-scope="scope">
                    <el-select v-model="scope.row.category_id" placeholder="Select"
                               @change="handleEdit(scope.$index, scope.row)">
                        <el-option
                                v-for="item in options"
                                :key="item.name"
                                :label="item.name"
                                :value="item.id">
                        </el-option>
                    </el-select>
                    <span>{{ scope.row.category_name }}</span>
                </template>
            </el-table-column>
        </el-table>

        <el-row type="flex" :gutter="20" justify="space-around">
            <el-col :span="8">
                <div class="grid-content">
                    合計金額
                    ¥
                    {{ total_value }}
                </div>
            </el-col>
            <el-col :span="10" :offset="2">
                <div class="grid-content">
                    <el-button type="primary" :disabled="Object.keys(editData).length === 0" @click="updateExpense">
                        保存
                    </el-button>
                    <el-button class="delete-button" type="danger" :disabled="deleteData.length === 0"
                               @click="confirmDelete">
                        削除
                    </el-button>
                </div>
            </el-col>
        </el-row>

        <el-dialog title="経費削除" :visible.sync="dialogVisible" width="80%">
            <span>選択した経費を削除しますか？</span>
            <el-table :data="deleteData">
                <el-table-column property="use_day" label="日付"></el-table-column>
                <el-table-column property="name" label="経費名"></el-table-column>
                <el-table-column property="payment" label="支払先"></el-table-column>
                <el-table-column property="category_name" label="経費項目"></el-table-column>
                <el-table-column property="value" label="値段"></el-table-column>
            </el-table>
            <span slot="footer" class="dialog-footer">
                <el-button @click="dialogVisible = false">Cancel</el-button>
                <el-button type="danger" @click="deleteExpense">Delete</el-button>
            </span>
        </el-dialog>
    </el-main>
</template>

<script>
    import http from '../../services/http'

    export default {
        props: {
            user_id: String
        },
        data() {
            return {
                options: [],
                periodList: [],
                period: '',
                tableData: [],
                userInfo: {},
                editData: {},
                deleteData: [],
                dialogVisible: false,
                periodLoading: false,
                listLoading: false
            }
        },
        computed: {
            total_value() {
                let total = 0;

                this.tableData.forEach((item) => {
                    if (Number(item.value) > 0) {
                        total += item.value;
                    }
                });

                // 3桁区切りで返す
                return String(total).replace(/(\d)(?=(\d\d\d)+(?!\d))/g, '$1,');
            }
        },
        created() {
            this.periodLoading = true;

            // TODO 同時に行えるように
            let periodUrl = 'users/' + this.user_id + '/period';

            http.get(periodUrl, res => {
                this.periodLoading = false;
                this.periodList = res.data;
            });

            http.get('categories', res => {
                this.options = res.data;
            });

            http.get('users/' + this.user_id, res => {
                this.userInfo = res.data;
            });
        },
        methods: {
            handleSelectionChange(val) {
                this.deleteData = val;
            },
            initialize() {
                // 初期化
                this.editData = {};
                this.deleteData = [];
            },
            getList() {
                let periodUrl = 'users/' + this.user_id + '/expenses/' + this.period;

                this.initialize();
                this.listLoading = true;
                http.get(periodUrl, res => {
                    this.tableData = res.data;
                    this.listLoading = false;
                }, error => {
                    console.log(error);
                })
            },
            handleEdit(index, row) {
                // 編集されたらデータを入れる
                this.$set(this.editData, row.id, row);
                console.log(index, row);
            },
            handleCurrentChange(row, event, column) {
                console.log(row, event, column, event.currentTarget)
            },
            deleteExpense() {
                this.dialogVisible = false;
                this.listLoading = true;

                let deleteData = {
                    deleteData: this.deleteData
                };

                let apiUrl = 'users/' + this.user_id + '/expenses';

                http.delete(apiUrl, deleteData, res => {
                    this.$message('削除しました');
                    this.getList();
                });
            },
            updateExpense() {
                let updateData = {
                    updateData: this.editData,
                    userId: this.user_id
                };

                let apiUrl = 'users/' + this.user_id + '/expenses';

                this.listLoading = true;
                http.put(apiUrl, updateData, res => {
                    this.$message({
                        message: '保存しました',
                        type: 'success'
                    });
                    this.getList();
                }, error => {
                    // TODO error handling
                    this.$message({
                        message: 'error',
                        type: 'error'
                    });
                    this.getList();
                });
            },
            confirmDelete() {
                this.dialogVisible = true;
            },
            csvDownload() {
                let csv_array = [
                    ['日付', '経費名', '支払先', '値段', '経費種類'],
                ];

                this.tableData.forEach((item) => {
                    csv_array.push(
                        [item.use_day, item.name, item.payment, item.value, item.category_name]
                    );
                });

                let element = document.createElement('a');
                let csvName = this.period + '_' + this.userInfo.name + '_expenses';

                let csv = [];
                csv_array.forEach(function (row) {
                    csv.push(row.join(','));
                });

                element.href = 'data:text/csv;charset=utf-8,%EF%BB%BF' + encodeURIComponent(csv.join('\n'));
                element.setAttribute('download', csvName);
                document.body.appendChild(element);
                element.click();
                document.body.removeChild(element);
            }
        }
    }
</script>

<style type="scss">
    .selector-wrapper {
        position: absolute;
        right: 20px;
        top: 10px;
    }

    .button-wrapper {
        position: absolute;
        right: 250px;
        top: 10px;
    }

    .el-row {
        box-sizing: border-box;
        height: 50px;
        padding-top: 10px;
    }

    .grid-content {
        align-items: center;
        color: #909399;
        display: flex;
        height: 100%;
        justify-content: center;
    }

    /* TODO scssに対応 */
    .tb-edit .el-input {
        display: none;
    }

    .tb-edit .current-row .el-input {
        display: block;
    }

    .tb-edit .current-row .el-input + span {
        display: none;
    }

    .tb-edit .el-select {
        display: none;
    }

    .tb-edit .current-row .el-select {
        display: block;
    }

    .tb-edit .current-row .el-select + span {
        display: none;
    }
</style>