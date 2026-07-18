<style>
    .sale-form-page {
        --primary: #4f46b8;
        --primary-hover: #4038a5;
        --primary-soft: #f1f0ff;
        --text-dark: #182033;
        --text-muted: #7b8498;
        --border: #e2e7ef;
        --danger: #d92d20;
        width: 100%;
        font-family: "Cairo", Arial, sans-serif;
    }

    .sale-form-page,
    .sale-form-page * {
        box-sizing: border-box;
    }

    .sale-form-page .page-heading {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 20px;
        width: 100%;
        margin-bottom: 24px;
    }

    .sale-form-page .back-link {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 12px;
        color: #667085;
        text-decoration: none;
        font-size: 13px;
        font-weight: 700;
        transition: .2s ease;
    }

    .sale-form-page .back-link:hover {
        color: var(--primary);
    }

    .sale-form-page .page-heading h1 {
        margin: 0 0 7px;
        color: var(--text-dark);
        font-size: 28px;
        font-weight: 800;
    }

    .sale-form-page .page-heading p {
        margin: 0;
        color: var(--text-muted);
        font-size: 13px;
    }

    .sale-form-page .form-card {
        width: 100%;
        max-width: none;
        overflow: hidden;
        border: 1px solid var(--border);
        border-radius: 20px;
        background: #fff;
        box-shadow: 0 12px 35px rgba(24, 32, 51, .06);
    }

    .sale-form-page .form-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 24px 28px;
        border-bottom: 1px solid var(--border);
        background:
            linear-gradient(
                135deg,
                rgba(79, 70, 184, .07),
                rgba(125, 160, 250, .025)
            );
    }

    .sale-form-page .form-card-header h2 {
        margin: 0 0 6px;
        color: var(--text-dark);
        font-size: 19px;
        font-weight: 800;
    }

    .sale-form-page .form-card-header p {
        margin: 0;
        color: var(--text-muted);
        font-size: 12px;
    }

    .sale-form-page .header-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 80px;
        padding: 8px 14px;
        border-radius: 25px;
        font-size: 11px;
        font-weight: 800;
    }

    .sale-form-page .header-status-active {
        border: 1px solid #bde8d3;
        background: #eaf9f2;
        color: #137a50;
    }

    .sale-form-page .header-status-inactive {
        border: 1px solid #f0d89f;
        background: #fff6df;
        color: #9a6700;
    }

    .sale-form-page .form-card-body {
        padding: 30px 28px;
    }

    .sale-form-page .form-intro {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 27px;
        padding-bottom: 21px;
        border-bottom: 1px solid var(--border);
    }

    .sale-form-page .form-intro h3 {
        margin: 0 0 5px;
        color: var(--text-dark);
        font-size: 17px;
        font-weight: 800;
    }

    .sale-form-page .form-intro p {
        margin: 0;
        color: var(--text-muted);
        font-size: 12px;
    }

    .sale-form-page .form-code-preview {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 120px;
        min-height: 39px;
        padding: 0 14px;
        border-radius: 10px;
        background: var(--primary-soft);
        color: var(--primary);
        font-size: 11px;
        font-weight: 800;
    }

    .sale-form-page .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 24px 22px;
        width: 100%;
    }

    .sale-form-page .form-group {
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 9px;
        margin: 0;
    }

    .sale-form-page .form-group-full {
        grid-column: 1 / -1;
    }

    .sale-form-page .form-group > label {
        display: block;
        margin: 0;
        color: #344054;
        font-size: 13px;
        font-weight: 800;
    }

    .sale-form-page .required {
        color: var(--danger);
    }

    .sale-form-page .form-group input {
        width: 100%;
        height: 50px;
        margin: 0;
        padding: 0 15px;
        border: 1px solid #dce1e9;
        border-radius: 12px;
        background: #fff;
        color: var(--text-dark);
        font-family: inherit;
        font-size: 13px;
        outline: none;
        transition: .2s ease;
    }

    .sale-form-page .form-group input:hover {
        border-color: #c3cad6;
    }

    .sale-form-page .form-group input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(79, 70, 184, .09);
    }

    .sale-form-page .form-group input::placeholder {
        color: #a2a9b5;
    }

    .sale-form-page .form-group input.input-error {
        border-color: #f04438;
        background: #fffafa;
    }

    .sale-form-page .error-message {
        color: var(--danger);
        font-size: 11px;
        font-weight: 700;
    }

    .sale-form-page .password-field {
        position: relative;
        width: 100%;
    }

    .sale-form-page .password-field input {
        padding-left: 82px;
    }

    .sale-form-page .toggle-password {
        position: absolute;
        top: 50%;
        left: 9px;
        transform: translateY(-50%);
        min-width: 60px;
        height: 34px;
        padding: 0 10px;
        border: 0;
        border-radius: 8px;
        background: #f1f3f7;
        color: #475467;
        font-family: inherit;
        font-size: 10px;
        font-weight: 800;
        cursor: pointer;
        transition: .2s ease;
    }

    .sale-form-page .toggle-password:hover {
        background: var(--primary-soft);
        color: var(--primary);
    }

    .sale-form-page .status-options {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 17px;
    }

    .sale-form-page .status-option {
        position: relative;
        display: block;
        margin: 0;
        cursor: pointer;
    }

    .sale-form-page .status-option input {
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
        pointer-events: none;
    }

    .sale-form-page .status-option-content {
        position: relative;
        min-height: 95px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 6px;
        padding: 18px 20px;
        border: 1px solid var(--border);
        border-radius: 14px;
        background: #fff;
        transition: .2s ease;
    }

    .sale-form-page .status-option-content::after {
        content: "";
        position: absolute;
        top: 17px;
        left: 17px;
        width: 18px;
        height: 18px;
        border: 2px solid #d0d5dd;
        border-radius: 50%;
        background: #fff;
    }

    .sale-form-page .status-option-content strong {
        color: var(--text-dark);
        font-size: 14px;
        font-weight: 800;
    }

    .sale-form-page .status-option-content small {
        color: var(--text-muted);
        font-size: 11px;
        font-weight: 500;
    }

    .sale-form-page .status-option:hover .status-option-content {
        transform: translateY(-2px);
        border-color: #c7cdd8;
    }

    .sale-form-page
    .status-option
    input:checked
    + .active-option {
        border-color: #22a06b;
        background: #f0fbf6;
        box-shadow: 0 0 0 4px rgba(34, 160, 107, .08);
    }

    .sale-form-page
    .status-option
    input:checked
    + .active-option::after {
        border: 5px solid #22a06b;
    }

    .sale-form-page
    .status-option
    input:checked
    + .inactive-option {
        border-color: #d69b18;
        background: #fffaef;
        box-shadow: 0 0 0 4px rgba(214, 155, 24, .08);
    }

    .sale-form-page
    .status-option
    input:checked
    + .inactive-option::after {
        border: 5px solid #d69b18;
    }

    .sale-form-page .form-actions {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 12px;
        padding: 20px 28px;
        border-top: 1px solid var(--border);
        background: #fafbfc;
    }

    .sale-form-page .cancel-button,
    .sale-form-page .save-button {
        min-width: 135px;
        min-height: 47px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 23px;
        border-radius: 11px;
        font-family: inherit;
        font-size: 13px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
        transition: .2s ease;
    }

    .sale-form-page .cancel-button {
        border: 1px solid #d8dde6;
        background: #fff;
        color: #344054;
    }

    .sale-form-page .cancel-button:hover {
        border-color: #bbc2ce;
        background: #f8f9fb;
    }

    .sale-form-page .save-button {
        border: 0;
        background: linear-gradient(
            135deg,
            var(--primary),
            #6560cc
        );
        color: #fff;
        box-shadow: 0 8px 20px rgba(79, 70, 184, .22);
    }

    .sale-form-page .save-button:hover {
        transform: translateY(-1px);
        background: linear-gradient(
            135deg,
            var(--primary-hover),
            #514bb8
        );
        box-shadow: 0 10px 25px rgba(79, 70, 184, .28);
    }

    @media (max-width: 900px) {
        .sale-form-page .form-card-header,
        .sale-form-page .form-card-body,
        .sale-form-page .form-actions {
            padding-right: 20px;
            padding-left: 20px;
        }
    }

    @media (max-width: 700px) {
        .sale-form-page .page-heading,
        .sale-form-page .form-card-header,
        .sale-form-page .form-intro {
            align-items: flex-start;
            flex-direction: column;
        }

        .sale-form-page .page-heading h1 {
            font-size: 24px;
        }

        .sale-form-page .form-grid,
        .sale-form-page .status-options {
            grid-template-columns: 1fr;
        }

        .sale-form-page .form-card-body {
            padding: 22px 16px;
        }

        .sale-form-page .form-actions {
            padding: 16px;
        }

        .sale-form-page .cancel-button,
        .sale-form-page .save-button {
            flex: 1;
            min-width: 0;
        }
    }

    @media (max-width: 430px) {
        .sale-form-page .form-actions {
            flex-direction: column-reverse;
        }

        .sale-form-page .cancel-button,
        .sale-form-page .save-button {
            width: 100%;
        }
    }
</style>