import 'dart:convert';
import 'package:http/http.dart' as http;

class PaypalServices {
  String domain = "https://api.sandbox.paypal.com"; // Use "https://api.paypal.com" for production
  String clientId = "YOUR_CLIENT_ID";
  String secret = "YOUR_SECRET_KEY";

  Future<String?> getAccessToken() async {
    try {
      // 1. Encode clientId:secret to base64 for Basic Auth
      String basicAuth = 'Basic ' + base64Encode(utf8.encode('$clientId:$secret'));
      var response = await http.post(
        Uri.parse('$domain/v1/oauth2/token?grant_type=client_credentials'),
        headers: {
          'Authorization': basicAuth,
          'Accept': 'application/json',
          'Accept-Language': 'en_US',
          'Content-Type': 'application/x-www-form-urlencoded',
        },
      );
      if (response.statusCode == 200) {
        final body = jsonDecode(response.body);
        return body["access_token"];
      }
      return null;
    } catch (e) {
      rethrow;
    }
  }

  Future<Map<String, String>?> createPaypalPayment(
      Map<String, dynamic> transactions, String accessToken) async {
    try {
      var response = await http.post(
        Uri.parse("$domain/v1/payments/payment"),
        body: jsonEncode(transactions),
        headers: {
          "content-type": "application/json",
          'Authorization': 'Bearer $accessToken'
        },
      );
      final body = jsonDecode(response.body);
      if (response.statusCode == 201) {
        List links = body["links"];
        String approvalUrl = links.firstWhere((o) => o["rel"] == "approval_url")["href"];
        String executeUrl = links.firstWhere((o) => o["rel"] == "execute")["href"];
        return {"executeUrl": executeUrl, "approvalUrl": approvalUrl};
      }
      throw Exception(body["message"]);
    } catch (e) {
      rethrow;
    }
  }
}