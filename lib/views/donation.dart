import 'package:flutter/material.dart';
import 'donate_tab.dart';
import 'my_donations_tab.dart';
import 'mentions_tab.dart';
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

  // Refactored: Just open the PayPal view, don't start payment yet
  void openPaypalView() {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => PaymentScreen(approvalUrl: "https://www.sandbox.paypal.com/checkoutnow"), // Placeholder URL
      ),
    );
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
                onDonate: () {
                  if (selectedPayment == 'Paypal') {
                    openPaypalView();
                  } else {
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