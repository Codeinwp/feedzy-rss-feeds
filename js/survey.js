/**
 * Initialize the formbricks survey.
 * 
 * @see https://github.com/formbricks/setup-examples/tree/main/html
 */
window.addEventListener('load', function () {
    window?.formbricks?.init?.({
        environmentId: "clskgehf78eu5podwdrnzciti",
        apiHost: "https://app.formbricks.com",
        ...(window?.feedzySurveyData ?? {}),
    });
});    