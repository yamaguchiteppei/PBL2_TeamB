document.addEventListener("DOMContentLoaded", () => {
    /* -----------------------------------
       1. 有償・無償の切り替え
       ----------------------------------- */
    const tradeRadios = document.querySelectorAll('input[name="trade"]');
    const priceField = document.getElementById("priceField");
    const priceInput = priceField.querySelector("input");

    const updatePriceField = () => {
        const checkedRadio = document.querySelector('input[name="trade"]:checked');
        if (checkedRadio && checkedRadio.value === "paid") {
            priceField.style.display = "block";
            priceInput.required = true;
        } else {
            priceField.style.display = "none";
            priceInput.value = "";
            priceInput.required = false;
        }
    };

    tradeRadios.forEach(radio => {
        radio.addEventListener("change", updatePriceField);
    });
    updatePriceField(); // 画面読み込み時にも実行

    /* -----------------------------------
       2. 学部・学科・コースの連動（データ定義）
       ----------------------------------- */
    const universityData = {
        "共通教育": { "共通教育": [] },
        "法文学部": {
            "人文社会学科": ["法学・政策学履修コース", "グローバル・スタディーズ履修コース", "人文学履修コース"]
        },
        "教育学部": {
            "学校教育教員養成課程": ["教育発達実践コース", "初等中等教科コース", "特別支援教育コース"]
        },
        "社会共創学部": {
            "産業マネジメント学科": [], "産業イノベーション学科": [], 
            "環境デザイン学科": [], "地域資源マネジメント学科": []
        },
        "理学部": {
            "理学科": ["数学・数理情報コース", "物理学コース", "化学コース", "生物学コース", "地学コース"]
        },
        "医学部": { "医学科": [], "看護学科": [] },
        "工学部": {
            "工学科": [
                "機械工学コース", "知能システム学コース", "電気電子工学コース", 
                "コンピュータ科学コース", "応用情報工学コース", "材料デザイン工学コース", 
                "化学・生命科学コース", "社会基盤工学コース", "社会デザインコース"
            ]
        },
        "農学部": { "食料生産学科": [], "生命機能学科": [], "生物環境学科": [] }
    };

    const facultySelect = document.getElementById('faculty_select');
    const departmentSelect = document.getElementById('department_select');
    const courseSelect = document.getElementById('course_select');

    // 選択肢をリセットする関数
    const resetSelect = (selectElement, defaultText) => {
        selectElement.innerHTML = '';
        selectElement.disabled = true; // 基本は無効化
        const defaultOption = document.createElement('option');
        defaultOption.value = "";
        defaultOption.text = defaultText;
        selectElement.appendChild(defaultOption);
    };

    // 選択肢を追加する関数
    const addOptions = (selectElement, items) => {
        items.forEach(item => {
            const option = document.createElement('option');
            option.value = item;
            option.text = item;
            selectElement.appendChild(option);
        });
        if (items.length > 0 || (items.length === 0 && selectElement === courseSelect)) {
             selectElement.disabled = false; // 中身があれば有効化
        }
    };

    /* -----------------------------------
       3. 選択時の処理（保存機能付き）
       ----------------------------------- */
    
    // 学部が変わったとき
    facultySelect.addEventListener('change', function() {
        const faculty = this.value;
        
        // 1. 記憶する
        sessionStorage.setItem('saved_faculty', faculty);
        sessionStorage.removeItem('saved_department'); // 下位の選択はクリア
        sessionStorage.removeItem('saved_course');

        // 2. 選択肢を更新
        updateDepartmentOptions(faculty);
    });

    // 学科が変わったとき
    departmentSelect.addEventListener('change', function() {
        const faculty = facultySelect.value;
        const department = this.value;

        // 1. 記憶する
        sessionStorage.setItem('saved_department', department);
        sessionStorage.removeItem('saved_course');

        // 2. 選択肢を更新
        updateCourseOptions(faculty, department);
    });

    // コースが変わったとき
    courseSelect.addEventListener('change', function() {
        sessionStorage.setItem('saved_course', this.value);
    });


    /* -----------------------------------
       4. 更新ロジックの分離
       ----------------------------------- */
    function updateDepartmentOptions(faculty) {
        resetSelect(departmentSelect, "学科を選択してください");
        resetSelect(courseSelect, "コースを選択してください");

        if (faculty && universityData[faculty]) {
            const departments = Object.keys(universityData[faculty]);
            if (departments.length > 0) {
                addOptions(departmentSelect, departments);
            } else {
                // 学科がない場合（共通教育など）
                addOptions(departmentSelect, ["なし"]);
                // 「なし」を自動選択扱いにしても良いが、ここではユーザーに選ばせるかそのまま
            }
        }
    }

    function updateCourseOptions(faculty, department) {
        resetSelect(courseSelect, "コースを選択してください");

        if (faculty && department && universityData[faculty][department]) {
            const courses = universityData[faculty][department];
            if (courses.length > 0) {
                addOptions(courseSelect, courses);
            } else {
                resetSelect(courseSelect, "コースはありません");
                courseSelect.disabled = true;
            }
        }
    }

    /* -----------------------------------
       5. 復元処理（戻るボタン対策の決定版）
       ----------------------------------- */
    const restoreSelections = () => {
        const savedFaculty = sessionStorage.getItem('saved_faculty');
        const savedDepartment = sessionStorage.getItem('saved_department');
        const savedCourse = sessionStorage.getItem('saved_course');

        // 学部が保存されていたら復元
        if (savedFaculty) {
            facultySelect.value = savedFaculty;
            // 学部の選択に合わせて学科リストを作る
            updateDepartmentOptions(savedFaculty);

            // 学科が保存されていたら復元
            if (savedDepartment) {
                departmentSelect.value = savedDepartment;
                // 学科の選択に合わせてコースリストを作る
                updateCourseOptions(savedFaculty, savedDepartment);

                // コースが保存されていたら復元
                if (savedCourse) {
                    courseSelect.value = savedCourse;
                }
            }
        }
    };

    // 画面が表示されたら（初回ロードでも、戻るボタンでも）復元を実行
    window.addEventListener('pageshow', restoreSelections);
    // 初回読み込み時にも念のため実行
    restoreSelections();
});
