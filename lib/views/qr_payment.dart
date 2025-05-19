import 'package:flutter/material.dart';

class QrPaymentScreen extends StatelessWidget {
  final String productName;
  final String totalAmount;
  final String commission;

  const QrPaymentScreen({
    super.key,
    required this.productName,
    required this.totalAmount,
    required this.commission,
  });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('QR Payment'),
      ),
      body: Padding(
        padding: const EdgeInsets.all(24.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // 1. Image at the top
            Padding(
              padding: const EdgeInsets.only(bottom: 24.0),
              child: Image.asset(
                'assets/logos/donate_logo.png',
                height: 100,
                fit: BoxFit.contain,
              ),
            ),
            // 2. Product name label
            const Text(
              'Product Name',
              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
            ),
            Text(
              productName,
              style: const TextStyle(fontSize: 16),
            ),
            const SizedBox(height: 16),
            // 3. Product total amount label
            const Text(
              'Total Amount',
              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
            ),
            Text(
              '\$$totalAmount',
              style: const TextStyle(fontSize: 16),
            ),
            const SizedBox(height: 16),
            // 4. Commission for donation label
            const Text(
              'Commission for Donation',
              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
            ),
            Text(
              '\$$commission',
              style: const TextStyle(fontSize: 16),
            ),
            // Remove the Spacer here to close the gap
            // const Spacer(),
            // 5. Payment button
            const SizedBox(height: 24), // Add a small space instead
            ElevatedButton.icon(
              onPressed: () {
                // TODO: Implement payment logic
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Payment processing...')),
                );
              },
              icon: const Icon(Icons.payment),
              label: const Text('Pay Now'),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFFED6F1D),
                foregroundColor: Colors.white,
                minimumSize: const Size(180, 48),
              ),
            ),
            // Image under Pay Now button
            const SizedBox(height: 24),
            Container(
              height: 260,
              decoration: BoxDecoration(
                border: Border.all(
                  color: Color(0xFFED6F1D), // UX/UI consistent color
                  width: 4,
                ),
                borderRadius: BorderRadius.circular(32),
              ),
              clipBehavior: Clip.antiAlias,
              child: Image.asset(
                'assets/UI/thanks.png',
                fit: BoxFit.contain,
              ),
            ),
          ],
        ),
      ),
    );
  }
}