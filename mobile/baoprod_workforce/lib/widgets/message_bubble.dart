import 'package:flutter/material.dart';
import '../models/chat_message.dart';

class MessageBubble extends StatefulWidget {
  final ChatMessage message;
  final VoidCallback? onLongPress;

  const MessageBubble({
    super.key,
    required this.message,
    this.onLongPress,
  });

  @override
  State<MessageBubble> createState() => _MessageBubbleState();
}

class _MessageBubbleState extends State<MessageBubble> with SingleTickerProviderStateMixin {
  late AnimationController _animationController;
  late Animation<Offset> _slideAnimation;
  late Animation<double> _fadeAnimation;

  @override
  void initState() {
    super.initState();
    _animationController = AnimationController(
      duration: const Duration(milliseconds: 300),
      vsync: this,
    );
    _slideAnimation = Tween<Offset>(
      begin: widget.message.isFromEmployee ? const Offset(1, 0) : const Offset(-1, 0),
      end: Offset.zero,
    ).animate(CurvedAnimation(
      parent: _animationController,
      curve: Curves.easeOut,
    ));
    _fadeAnimation = Tween<double>(
      begin: 0.0,
      end: 1.0,
    ).animate(_animationController);

    _animationController.forward();
  }

  @override
  void dispose() {
    _animationController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return SlideTransition(
      position: _slideAnimation,
      child: FadeTransition(
        opacity: _fadeAnimation,
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
          child: Row(
            mainAxisAlignment: widget.message.isFromEmployee
                ? MainAxisAlignment.end
                : MainAxisAlignment.start,
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              if (!widget.message.isFromEmployee && !widget.message.isSystemMessage) ...[
                _buildAvatar(),
                const SizedBox(width: 8),
              ],
              Flexible(
                child: GestureDetector(
                  onLongPress: widget.onLongPress,
                  child: Container(
                    constraints: BoxConstraints(
                      maxWidth: MediaQuery.of(context).size.width * 0.75,
                    ),
                    child: Column(
                      crossAxisAlignment: widget.message.isFromEmployee
                          ? CrossAxisAlignment.end
                          : CrossAxisAlignment.start,
                      children: [
                        if (!widget.message.isSystemMessage) ...[
                          Padding(
                            padding: const EdgeInsets.only(bottom: 4),
                            child: Text(
                              widget.message.senderName,
                              style: TextStyle(
                                fontSize: 11,
                                color: Colors.grey[600],
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                          ),
                        ],
                        _buildMessageContent(),
                        const SizedBox(height: 2),
                        Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Text(
                              widget.message.formattedTime,
                              style: TextStyle(
                                fontSize: 10,
                                color: Colors.grey[500],
                              ),
                            ),
                            if (widget.message.isFromEmployee) ...[
                              const SizedBox(width: 4),
                              Icon(
                                widget.message.isRead ? Icons.done_all : Icons.done,
                                size: 12,
                                color: widget.message.isRead 
                                    ? Theme.of(context).primaryColor 
                                    : Colors.grey[500],
                              ),
                            ],
                          ],
                        ),
                      ],
                    ),
                  ),
                ),
              ),
              if (widget.message.isFromEmployee) ...[
                const SizedBox(width: 8),
                _buildAvatar(),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildAvatar() {
    if (widget.message.isSystemMessage) {
      return Container(
        width: 32,
        height: 32,
        decoration: BoxDecoration(
          color: Colors.blue.withOpacity(0.1),
          shape: BoxShape.circle,
        ),
        child: const Icon(
          Icons.info,
          color: Colors.blue,
          size: 16,
        ),
      );
    }

    return Container(
      width: 32,
      height: 32,
      decoration: BoxDecoration(
        color: widget.message.isFromEmployee
            ? Theme.of(context).primaryColor.withOpacity(0.1)
            : Colors.grey.withOpacity(0.1),
        shape: BoxShape.circle,
      ),
      child: Icon(
        widget.message.isFromEmployee ? Icons.person : Icons.business_center,
        color: widget.message.isFromEmployee
            ? Theme.of(context).primaryColor
            : Colors.grey[600],
        size: 16,
      ),
    );
  }

  Widget _buildMessageContent() {
    Color backgroundColor;
    Color textColor;

    if (widget.message.isSystemMessage) {
      backgroundColor = Colors.blue.withOpacity(0.1);
      textColor = Colors.blue.shade700;
    } else if (widget.message.isFromEmployee) {
      backgroundColor = Theme.of(context).primaryColor;
      textColor = Colors.white;
    } else {
      backgroundColor = Colors.grey.shade100;
      textColor = Colors.black87;
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      decoration: BoxDecoration(
        color: backgroundColor,
        borderRadius: BorderRadius.circular(18).copyWith(
          bottomRight: widget.message.isFromEmployee 
              ? const Radius.circular(4) 
              : const Radius.circular(18),
          bottomLeft: !widget.message.isFromEmployee && !widget.message.isSystemMessage
              ? const Radius.circular(4)
              : const Radius.circular(18),
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 3,
            offset: const Offset(0, 1),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildTextContent(textColor),
          if (widget.message.imageUrl != null) ...[
            const SizedBox(height: 8),
            _buildImageContent(),
          ],
          if (widget.message.documentUrl != null) ...[
            const SizedBox(height: 8),
            _buildDocumentContent(),
          ],
        ],
      ),
    );
  }

  Widget _buildTextContent(Color textColor) {
    return Text(
      widget.message.content,
      style: TextStyle(
        color: textColor,
        fontSize: 14,
        fontWeight: widget.message.isRead || widget.message.isFromEmployee 
            ? FontWeight.normal 
            : FontWeight.w500,
      ),
    );
  }

  Widget _buildImageContent() {
    return Container(
      width: 200,
      height: 150,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(8),
        color: Colors.grey.shade200,
      ),
      child: const Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.image, size: 32, color: Colors.grey),
            SizedBox(height: 4),
            Text('Image', style: TextStyle(color: Colors.grey)),
          ],
        ),
      ),
    );
  }

  Widget _buildDocumentContent() {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.2),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(
          color: Colors.white.withOpacity(0.3),
          width: 1,
        ),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(
            Icons.description,
            color: widget.message.isFromEmployee ? Colors.white : Colors.grey[700],
            size: 20,
          ),
          const SizedBox(width: 8),
          Flexible(
            child: Text(
              'Document joint',
              style: TextStyle(
                color: widget.message.isFromEmployee ? Colors.white : Colors.grey[700],
                fontSize: 12,
                fontWeight: FontWeight.w500,
              ),
            ),
          ),
        ],
      ),
    );
  }
}