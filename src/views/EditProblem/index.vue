<template>
  <div class="app-container">
    <div class="info">
      <el-alert
        v-if="id == undefined"
        title="你正在添加新题目"
        type="success"
        effect="dark"
        style="margin-bottom: 20px"
      ></el-alert>
      <el-alert
        v-else
        :title="'你正在修改题目，编号为: ' + id "
        type="warning"
        effect="dark"
        style="margin-bottom: 20px"
      >
        <el-button type="text" size="small" @click="back">返回列表</el-button>
      </el-alert>
      <el-form>
        <el-form-item label="题型">
          <el-select size="small" v-model="data.type" placeholder="请选择" @change="chooseType">
            <el-option
              v-for="(item, index) in type_arr"
              :label="item"
              :value="index"
              :key="'op_' + index"
            ></el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="科目">
          <el-select size="small" v-model="data.subject" placeholder="请选择">
            <el-option
              v-for="(value,key,index) in subject_obj"
              :label="value"
              :value="key"
              :key="'op_' + index"
            ></el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="标签">
          <!-- 未处理重复标签 -->
          <el-tag
            :key="tag"
            v-for="tag in data.tags"
            closable
            :disable-transitions="false"
            @close="handleClose(tag)"
          >{{tag}}</el-tag>
          <el-input
            class="input-new-tag"
            v-if="inputVisible"
            v-model="inputValue"
            ref="saveTagInput"
            size="small"
            @keyup.enter.native="handleInputConfirm"
            @blur="handleInputConfirm"
          ></el-input>
          <el-button
            v-else
            class="button-new-tag"
            size="small"
            @click="showInput"
            :disabled="data.tags.length >= 5"
          >+ 添加标签</el-button>
        </el-form-item>
        <el-form-item label="状态">
          <el-switch v-model="data.public" :active-text="data.public? '公开':'私有'"></el-switch>
        </el-form-item>
      </el-form>

      <!-- 第三步 -->
      <div>
        <h4>题目描述：</h4>
        <Editor ref="editor" v-model="data.description" style="margin-top: 20px"></Editor>
        <div v-if="data.type == 0 || data.type == 1" style="margin-top: 20px">
          <!-- 选择题 -->
          <h4>选项：</h4>
          <el-alert title="以换行符分隔每个选项，然后点击按钮生成；选中以指定正确答案" type="warning"></el-alert>
          <br />
          <el-input
            type="textarea"
            :rows="10"
            class="options_input"
            placeholder="请输入选项内容，每项一行"
            v-model="options_msg"
            v-if="edit_options"
          ></el-input>
          <div v-else>
            <!-- 单选题 -->
            <div v-if="data.type == 0">
              <div v-for="(item, index) in data.options.arr" :key="index+'_op'">
                <div v-if="data.type == 0" class="options_div">
                  <el-radio
                    v-model="data.options.right"
                    :label="index"
                  >{{ String.fromCharCode( 65 + index ) }} .</el-radio>
                  <span style="font-size: 14px">{{ item }}</span>
                </div>
              </div>
            </div>
            <!-- 多选题 -->
            <div v-if="data.type == 1">
              <el-checkbox-group v-model="data.options.right">
                <div
                  v-for="(item, index) in data.options.arr"
                  :key="index+'_op'"
                  class="options_div"
                >
                  <el-checkbox :label="index">{{ String.fromCharCode( 65 + index ) }} .</el-checkbox>
                  <span style="font-size: 14px">{{ item }}</span>
                </div>
              </el-checkbox-group>
            </div>
          </div>
          <div style="margin-top: 20px" v-if="data.type <= 1">
            <el-button
              type="primary"
              plain
              size="small"
              @click="generateOptions"
              v-show="edit_options"
              :disabled="options_msg.trim().length == 0"
            >生成选项</el-button>
          </div>
          <div>
            <el-button
              plain
              size="small"
              type="default"
              @click="edit_options = true"
              v-show="!edit_options"
            >重新编辑</el-button>
          </div>
        </div>
        <!-- 判断题  -->
        <div v-if="data.type == 2">
          <h3>指定答案：</h3>
          <el-radio v-model="data.options.right" label="0">对</el-radio>
          <el-radio v-model="data.options.right" label="1">错</el-radio>
        </div>
        <!-- 简答题 -->
        <div v-if="data.type == 3">
          <h4>参考答案：</h4>
          <el-input
            type="textarea"
            :rows="10"
            class="options_input"
            placeholder="请输入参考答案"
            v-model="data.options.right"
          ></el-input>
        </div>
      </div>
    </div>
    <el-button type="success" style="margin: 50px 0; width: 100%" @click="dialogVisible = true">预览题目</el-button>
    <el-dialog title="预览" :visible.sync="dialogVisible" width="60%">
      <span>{{ type_arr[data.type] }}</span>
      |
      <span>{{ subject_obj[data.subject] }}</span>
      |
      <span v-if="data.public">公开</span>
      <span v-else style="color: orange">私有</span>
      <div style="margin-top: 10px">
        <el-tag size="small" v-for="(item, index) in data.tags" :key="'tag_' + index">{{ item }}</el-tag>
      </div>

      <div style="margin-top: 20px">
        <div v-html="data.description" v-highlight></div>
        <div v-if="data.type == 0">
          <!-- 单选题的正确答案 -->
          <div
            v-for="(item, index) in data.options.arr"
            :key="'op_' + index"
            style="display: flex; margin: 5px 0"
            :class="{ 'right_op': data.options.right == index}"
          >
            <div
              style="white-space: nowrap;display: inline-block;"
            >{{ String.fromCharCode( 65 + index ) }}&nbsp;.&nbsp;</div>
            <span>{{ item }}</span>
          </div>
        </div>
        <div v-if="data.type == 1">
          <!-- 多选题的正确答案 -->
          <div
            v-for="(item, index) in data.options.arr"
            :key="'op_' + index"
            style="display: flex; margin: 5px 0"
            :class="{ 'right_op': data.options.right.indexOf(index) != -1}"
            class="margin-left"
          >
            <div
              style="white-space: nowrap;display: inline-block;"
            >{{ String.fromCharCode( 65 + index ) }}&nbsp;.&nbsp;</div>
            <span>{{ item }}</span>
          </div>
        </div>
        <div v-if="data.type == 2">
          <!-- 判断题的正确答案 -->
          <div class="right_op">{{ data.options.right == 0? '正确':'错误' }}</div>
        </div>
        <div v-if="data.type == 3">
          <!-- 简答题的正确答案 -->
          <span>参考答案：</span>
          <div class="right_op">{{ data.options.right }}</div>
        </div>
      </div>

      <span slot="footer" class="dialog-footer">
        <el-button @click="dialogVisible = false">返 回</el-button>
        <el-button type="primary" @click="post" :loading="postLoading">确 定</el-button>
      </span>
    </el-dialog>
  </div>
</template>
<script>
import Editor from "@/components/TinyMce";
import Qs from "qs";
import { mapGetters } from "vuex";

export default {
  data() {
    return {
      options_msg: "",
      edit_options: true,
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
      data: {
        type: "", //题型
        subject: "",
        tags: ["LZGZ_EXAM"],
        public: true,
        description: "",
        options: {
          num: "", // 选项总数
          right: null, // 正确答案
          arr: "" // 选项内容
        }
      },
      inputVisible: false,
      inputValue: "",
      type_arr: ["单项选择题", "多项选择题", "判断题", "简答题"],
      dialogVisible: false,
      postLoading: false,
      id: undefined
    };
  },
  mounted() {
    this.id = this.$route.query.id;
    if (this.id != undefined) {
      //编辑
      this.$axios
        .get(this.api, {
          params: {
            action: "getProblemsDetail",
            id: this.id
          }
        })
        .then(res => {
          let data = res.data.msg;
          //修正数据类型
          data.type = Number(data.type);
          data.public = Boolean(data.public);
          data.tags = this.strToJson(data.tags);
          data.options = this.strToJson(data.options);

          if (data.type != 3) {
            this.options_msg = data.options.arr.join("\n");
            data.options.right = Number(data.options.right);
          } else this.options_msg = data.options.right;
          this.edit_options = false;
          this.data = data;
        });
    }
  },
  methods: {
    post() {
      this.postLoading = true;
      this.data["author"] = this.name;
      this.$axios
        .post(this.api + "?action=newProblem", Qs.stringify(this.data))
        .then(res => {
          if (res.data.code == 200) {
            this.postLoading = false;
            this.dialogVisible = false;
            if (this.id != undefined) {
              //完成编辑
              this.$router.push({
                path: "/list-problems"
              });
            } else {
              //完成导入，提示是否继续
              this.$confirm("添加题目成功！是否以相同配置继续添加？", "提示", {
                confirmButtonText: "确定",
                cancelButtonText: "取消",
                type: "success"
              })
                .then(() => {
                  //初始化部分题目数据
                  this.data.description = "";
                  this.data.options = {
                    num: "", // 选项总数
                    right: this.data.type == 1 ? [] : "", // 正确答案
                    arr: ""
                  };
                  //初始化编辑界面
                  this.options_msg = "";
                  this.edit_options = true;
                })
                .catch(() => {
                  //初始化全部信息
                  this.data = {
                    type: "", //题型
                    subject: "",
                    tags: ["LZGZ_EXAM"],
                    public: true,
                    description: "",
                    options: {
                      num: "", // 选项总数
                      right: null, // 正确答案
                      arr: "" // 选项内容
                    }
                  };
                  this.options_msg = "";
                  this.edit_options = true;
                });
            }
          } else {
            console.log(res.data);
            this.postLoading = false;
          }
        })
        .catch(err => {
          console.log(err);
        });
    },
    chooseType(value) {
      if (value == 1) this.data.options.right = [];
      else this.data.options.right = "";
    },
    handleClose(tag) {
      this.data.tags.splice(this.data.tags.indexOf(tag), 1);
    },

    showInput() {
      this.inputVisible = true;
      this.$nextTick(_ => {
        this.$refs.saveTagInput.$refs.input.focus();
      });
    },

    handleInputConfirm() {
      let inputValue = this.inputValue;
      if (inputValue) {
        this.data.tags.push(inputValue);
      }
      this.inputVisible = false;
      this.inputValue = "";
    },

    generateOptions() {
      //生成选项
      this.edit_options = false;
      let arr_tmp = this.options_msg.split("\n");
      let arr = [];
      for (let index in arr_tmp) {
        if (arr_tmp[index].length > 0) {
          arr.push(arr_tmp[index]);
        }
      }
      this.$set(this.data.options, "num", arr.length);
      this.$set(this.data.options, "arr", arr);
    },
    back() {
      this.$router.go(-1);
    },
    strToJson(objStr) {
      //字符串转json
      return new Function("return " + objStr)();
    }
  },
  computed: {
    ...mapGetters(["name"])
  },
  components: {
    Editor
  }
};
</script>
<style lang="scss" scoped>
.el-tag + .el-tag {
  margin-left: 10px;
}
.button-new-tag {
  margin-left: 10px;
  height: 32px;
  line-height: 30px;
  padding-top: 0;
  padding-bottom: 0;
}
.input-new-tag {
  width: 90px;
  margin-left: 10px;
  vertical-align: bottom;
}
.btn_group {
  margin: 20px 0;
  text-align: center;
}
.app-container >>> .tox-statusbar__resize-handle {
  display: none;
}
.link-top {
  height: 1px;
  border-top: solid #acc0d89f 1px;
  margin: 30px 0;
}
.options_div {
  margin-bottom: 20px;
  display: flex;
}
.options_input >>> textarea {
  resize: none;
}
.right_op {
  color: #67c23a;
  // font-weight: bold;
  margin-left: 20px;
}
</style>

