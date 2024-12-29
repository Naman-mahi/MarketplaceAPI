## Instructions for Using Links in WebView

Below are the URLs you can integrate into the WebView in your application. The **Test** sections contain static, hardcoded links, while the **Use** sections contain dynamic URLs with placeholders `{username}` and `{organization_id}` that should be replaced with actual values based on user input or data.

### Employee:

**Test:**
- [Employee Card](https://proffid.com/employee-card/employee)

**Use:**
- [Employee Card with Username](https://proffid.com/employee-card/{username})

**Test:**
- [Employee Business Card](https://proffid.com/employee-business-card/employee)

**Use:**
- [Employee Business Card with Username](https://proffid.com/employee-business-card/{username})

### Company:

**Test:**
- [Company Business Card - KenzWheels](https://proffid.com/company-business-card/KenzWheels)

**Use:**
- [Company Business Card with Username](https://proffid.com/company-business-card/{username})

**Test:**
- [Company Digital ID - KenzWheels](https://proffid.com/digital-id-company/KenzWheels)

**Use:**
- [Company Digital ID with Username](https://proffid.com/digital-id-company/{username})

### User:

**Test:**
- [User Card with Organization ID](https://proffid.com/card/user/7)

**Use:**
- [User Card with Username](https://proffid.com/card/{username}/{organization_id})

**Test:**
- [User Business Card](https://proffid.com/business-card/user/7)

**Use:**
- [User Business Card with Username and Organization ID](https://proffid.com/business-card/{username}/{organization_id})

---

### How to Use in WebView:
1. **For Static Links (Test)**: Use these links directly as URLs in your WebView to display predefined content.
   
2. **For Dynamic Links (Use)**: Replace the placeholders `{username}` and `{organization_id}` in the URLs with the actual values dynamically. For example:
   - Replace `{username}` with the user’s actual username.
   - Replace `{organization_id}` with the relevant organization ID.

3. **Integration in WebView**: To load the URLs in your WebView component, you can use code similar to the following:
   ```javascript
   webview.loadUrl("https://proffid.com/employee-card/" + username);
