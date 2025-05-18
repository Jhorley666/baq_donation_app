import 'package:flutter/material.dart';
import 'donate_tab.dart';
import 'my_donations_tab.dart';
import 'mentions_tab.dart';
import 'paypal_services.dart';
import 'paypal.dart';

class DonationScreen extends StatefulWidget {
  const DonationScreen({super.key});

  @override
  State<DonationScreen> createState() => _DonationScreenState();
}

class _DonationScreenState extends State<DonationScreen> {
  final List<String> paymentTypes = [
    'Scan QR',
    'Paypal',
    'Stripe',
    'Plux',
    'Datafast',
    'Other',
  ];

  String? selectedPayment;
  bool showOtherImage = false;

  // 1. Paypal payment logic
  void makePayment() async {
    final service = PaypalServices();
    final accessToken = await service.getAccessToken();
    if (accessToken != null) {
      final transactions = {
        "intent": "sale",
        "payer": {"payment_method": "paypal"},
        "transactions": [
          {
            "amount": {"total": "10.00", "currency": "USD"},
            "description": "Payment for items"
          }
        ],
        "redirect_urls": {
          "return_url": "YOUR_RETURN_URL",
          "cancel_url": "YOUR_CANCEL_URL"
        }
      };
      final payment = await service.createPaypalPayment(transactions, accessToken);
      if (payment != null) {
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) => PaymentScreen(approvalUrl: payment["approvalUrl"]!),
          ),
        ).then((result) {
          if (result == "success") {
            print("Payment successful");
          } else {
            print("Payment cancelled");
          }
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 3,
      child: Scaffold(
        appBar: AppBar(
          title: const Text('Donation'),
          bottom: const TabBar(
            tabs: [
              Tab(text: 'Donate', icon: Icon(Icons.volunteer_activism)),
              Tab(text: 'My donations', icon: Icon(Icons.bar_chart)),
              Tab(text: 'Mentions', icon: Icon(Icons.people)),
            ],
          ),
        ),
        body: TabBarView(
          children: [
            // Donate Tab
            Padding(
              padding: const EdgeInsets.all(24.0),
              child: DonateTab(
                paymentTypes: paymentTypes,
                selectedPayment: selectedPayment,
                showOtherImage: showOtherImage,
                onPaymentChanged: (value) {
                  if (value == 'Scan QR') {
                    Navigator.pushNamed(context, '/qr-scan');
                    return;
                  }
                  setState(() {
                    selectedPayment = value;
                    showOtherImage = value == 'Other';
                  });
                },
                // Add onDonate callback
                onDonate: () {
                  if (selectedPayment == 'Paypal') {
                    makePayment();
                  } else {
                    // Handle other payment types as before
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(content: Text('Donating with $selectedPayment')),
                    );
                  }
                },
              ),
            ),
            // My Donations Tab
            const Padding(
              padding: EdgeInsets.all(24.0),
              child: MyDonationsTab(),
            ),
            // Mentions Tab
            const Padding(
              padding: EdgeInsets.all(24.0),
              child: MentionsTab(),
            ),
          ],
        ),
      ),
    );
  }
}