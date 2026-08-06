const CLICKID_PATTERN = /^[A-Za-z0-9_-]{1,128}$/;

/** Reject unfired macros ({clickid}, [gclid]) and anything outside the safe charset. */
const sanitizeClickid = (value) => {
    if (!value || typeof value !== "string") return null;
    const trimmed = value.trim();
    if (/^[{[].*[}\]]$/.test(trimmed)) return null;
    return CLICKID_PATTERN.test(trimmed) ? trimmed : null;
};

const readClickid = () => {
    const params = new URLSearchParams(window.location.search);
    return (
        sanitizeClickid(params.get("rtkcid")) ||
        sanitizeClickid(getCookie("rtkclickid-store")) ||
        sanitizeClickid(localStorage.getItem("rtkclickid"))
    );
};

const applyClickidToOfferLinks = (clickid) => {
    if (!clickid) return;

    document.querySelectorAll("a[href]").forEach((el) => {
        const href = el.getAttribute("href");
        if (!href || /^(#|mailto:|tel:|javascript:)/i.test(href)) return;

        try {
            const url = new URL(href, window.location.origin);
            if (!url.hostname.endsWith("nordace.com")) return;
            if (url.searchParams.has("clickid")) return;

            url.searchParams.set("clickid", clickid);
            el.href = url.toString();
        } catch {
            /* malformed href — leave untouched */
        }
    });
};
