import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';

class PaymentScreen extends StatefulWidget {
  final String approvalUrl;
  PaymentScreen({required this.approvalUrl});

  @override
  _PaymentScreenState createState() => _PaymentScreenState();
}

class _PaymentScreenState extends State<PaymentScreen> {
  late final WebViewController _controller;

  @override
  void initState() {
    super.initState();
    _controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setNavigationDelegate(
        NavigationDelegate(
          onNavigationRequest: (NavigationRequest request) {
            if (request.url.contains("return_url")) {
              Navigator.pop(context, "success");
              return NavigationDecision.prevent;
            } else if (request.url.contains("cancel_url")) {
              Navigator.pop(context, "cancel");
              return NavigationDecision.prevent;
            }
            return NavigationDecision.navigate;
          },
        ),
      )
      ..loadRequest(Uri.parse(widget.approvalUrl));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text("PayPal Payment")),
      body: WebViewWidget(controller: _controller),
    );
  }
}