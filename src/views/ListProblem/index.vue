<template>
  <div class="content">
    <div style="margin-bottom: 20px; text-align: right">
      <el-select
        size="small"
        v-model="type"
        placeholder="题型"
        @change="chooseType"
        style="width:120px"
      >
        <el-option
          v-for="(item, index) in type_arr"
          :label="item"
          :value="index"
          :key="'op_' + index"
        ></el-option>
      </el-select>
      <el-select
        size="small"
        v-model="subject"
        placeholder="科目"
        @change="chooseType"
        style="width:120px"
      >
        <el-option
          v-for="(value,key,index) in subject_obj"
          :label="value"
          :value="key"
          :key="'op_' + index"
        ></el-option>
      </el-select>
      <el-input
        class="search_input"
        placeholder="请输入内容"
        prefix-icon="el-icon-search"
        v-model="keyWords"
        size="small"
      ></el-input>
      <el-button
        type="primary"
        size="small"
        :disabled="type.length ==0 && subject.length ==0 && keyWords.length ==0"
      >搜 索</el-button>
    </div>
    <div style="text-align: center; margin-bottom: 20px">
      <el-pagination
        background
        layout="prev, pager, next"
        :current-page.sync="page_data.current + 1"
        :total="page_data.total"
        :size="page_data.size"
        @current-change="pageChange"
      ></el-pagination>
    </div>
    <el-card>
      <el-table
        :data="tableData"
        v-loading="loading"
        default-expand-all
        :header-cell-style="{color: 'black', 'text-align': 'center'}"
        :cell-style="{'text-align': 'center'}"
      >
        <el-table-column type="expand" label="#">
          <template slot-scope="props">
            <div v-html="props.row.description" v-highlight></div>
            <div v-if="props.row.type == 0">
              <!-- 单选题的正确答案 -->
              <div
                v-for="(item, index) in props.row.options.arr"
                :key="'op_' + index"
                style="display: flex; margin: 5px 0"
                :class="{ 'right_op': props.row.options.right == index}"
              >
                <div
                  style="white-space: nowrap;display: inline-block;"
                >{{ String.fromCharCode( 65 + index ) }}&nbsp;.&nbsp;</div>
                <span>{{ item }}</span>
              </div>
            </div>
            <div v-if="props.row.type == 1">
              <!-- 多选题的正确答案 -->
              <div
                v-for="(item, index) in props.row.options.arr"
                :key="'op_' + index"
                style="display: flex; margin: 5px 0"
                :class="{ 'right_op': props.row.options.right.indexOf(index) != -1}"
                class="margin-left"
              >
                <div
                  style="white-space: nowrap;display: inline-block;"
                >{{ String.fromCharCode( 65 + index ) }}&nbsp;.&nbsp;</div>
                <span>{{ item }}</span>
              </div>
            </div>
            <div v-if="props.row.type == 2">
              <!-- 判断题的正确答案 -->
              <div class="right_op">{{ props.row.options.right == 0? '正确':'错误' }}</div>
            </div>
            <div v-if="props.row.type == 3">
              <!-- 简答题的正确答案 -->
              <span>参考答案：</span>
              <div class="right_op">{{ props.row.options.right }}</div>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="id" label="编号" width="80"></el-table-column>
        <el-table-column prop="type" label="题型" width="120">
          <template slot-scope="props">
            <div v-html="type_arr[props.row.type]"></div>
          </template>
        </el-table-column>
        <el-table-column prop="subject" label="科目" width="120">
          <template slot-scope="props">
            <div v-html="subject_obj[props.row.subject]"></div>
          </template>
        </el-table-column>
        <el-table-column prop="tags" label="标签">
          <template slot-scope="props">
            <el-tag
              v-for="(item,index) in strToJson(props.row.tags)"
              :key="index"
              size="small"
              style="margin-right: 10px"
            >{{ item }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="actions" label="操作" width="200">
          <template slot-scope="props">
            <div>
              <el-tooltip
                class="item"
                effect="dark"
                content="添加至考试"
                placement="top"
                style="margin-right: 15px; cursor: pointer;font-size: 18px"
              >
                <i class="el-icon-plus btn"></i>
              </el-tooltip>
              <el-tooltip
                class="item"
                effect="dark"
                content="编辑"
                placement="top"
                style="margin-right: 15px; cursor: pointer;font-size: 18px"
              >
                <i class="el-icon-edit btn" @click="editProblem(props.row.id)"></i>
              </el-tooltip>
              <el-tooltip
                class="item"
                effect="dark"
                content="添加至收藏"
                placement="top"
                style="margin-right: 15px; cursor: pointer;font-size: 18px"
              >
                <i class="el-icon-star-off btn"></i>
              </el-tooltip>
            </div>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
    <div style="text-align: center; margin: 20px 0">
      <el-pagination
        background
        layout="prev, pager, next"
        :current-page.sync="page_data.current + 1"
        :total="page_data.total"
        :size="page_data.size"
        @current-change="pageChange"
      ></el-pagination>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      type: "",
      subject: "",
      keyWords: "",
      tableData: [],
      type_arr: ["单项选择题", "多项选择题", "判断题", "简答题"],
      subject_obj: {
        yw: "语文",
        sx: "数学",
        yy: "英语",
        wl: "物理",
        hx: "化学",
        sw: "生物",
        ls: "历史",
        zz: "政治",
        dl: "地理",
        xx: "信息技术",
        xl: "心理",
        ys: "艺术",
        qt: "其他"
      },
      page_data: {
        size: 10,
        total: 0,
        current: 0
      },
      loading: true
    };
  },
  mounted() {
    this.getList();
  },
  methods: {
    handleDsp(str) {
      //处理描述的函数
      str = str.replace(/<[^>]+>/g, "");
      if (str.length > 40) return str.substr(0, 40) + "...";
      return str;
    },
    chooseType() {},
    strToJson(objStr) {
      //字符串转json
      return new Function("return " + objStr)();
    },
    editProblem(id) {
      this.$router.push({
        path: `/edit-problems/?id=${id}`
      });
    },
    getList() {
      this.loading = true;
      this.$axios
        .get(this.api, {
          params: {
            action: "getProblemsList",
            page: this.page_data.current,
            size: this.page_data.size
          }
        })
        .then(res => {
          let data = res.data.msg;
          for (var i in data) {
            data[i].options = this.strToJson(data[i].options);
          }
          this.tableData = data;
          this.page_data.total = Number(res.data.total);
          this.loading = false;
        })
        .catch(err => {
          console.log(err);
        });
    },
    pageChange(c) {
      this.page_data.current = c - 1;
      this.getList();
    }
  }
};
</script>
<style scoped>
.content {
  position: relative;
  margin: 20px;
}
.search_input {
  width: 240px;
}
.box-card {
  margin-bottom: 20px;
}
.card_header_label {
  color: #606266;
  font-size: 14px;
}
.card_header_text {
  font-size: 14px;
  margin-right: 20px;
}
.right_op {
  color: #67c23a;
  /* font-weight: bold; */
  margin-left: 20px;
}
.btn:hover {
  color: #409eff;
}
.loading {
  position: relative;
  top: 0;
  left: 0;
  z-index: 999;
  background: white;
  height: 500px;
  width: 100%;
}
</style>